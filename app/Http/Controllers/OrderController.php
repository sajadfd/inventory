<?php

namespace App\Http\Controllers;

use App\Enums\NotificationType;
use App\Enums\OrderStatusEnum;
use App\Enums\PermissionEnum;
use App\Enums\SaleType;
use App\Enums\UserType;
use App\Http\ApiResponse;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Requests\ConfirmOrderRequest;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\OrderItem;
use App\Models\ProductTransaction;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\User;
use App\Services\PaginatorService;
use App\Services\ProductStoreService;
use Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\QueryBuilder\QueryBuilder;

class OrderController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Order::class, 'order');
    }

    public function index()
    {
        $order = Order::query()
            ->when(Auth::user()->type === UserType::Customer, fn($query) => $query->where('customer_id', Auth::user()->customer->id))
            ->when(Auth::user()->type === UserType::Driver, fn($query) => $query->where('driver_id', Auth::user()->driver->id));
        return ApiResponse::success(PaginatorService::from($order, OrderResource::class, useQueryBuilder: function (QueryBuilder &$queryBuilder) {
            $queryBuilder->allowedFilters(['status']);
        }));
    }

    public function show(Order $order)
    {
        return ApiResponse::success(OrderResource::make($order));
    }

    public function confirm(ConfirmOrderRequest $request, Order $order)
    {
        $this->authorize('confirm', $order);
        \DB::transaction(function () use ($request, &$order) {
            $order->update($request->validated());
            $order->confirm();
            if ($driver = $order->driver) {
                $driver->user->notify(__("New order to deliver!"), __("New order for customer :name to deliver to :address", ['name' => $order->customer->name, 'address' => $order->end_address]), NotificationType::OrderToDeliver);
            }
            $order->customer->user?->notify(__("Order confirmed"), __("Order confirmed"), NotificationType::OrderConfirmed);

        });

        return ApiResponse::success(OrderResource::make($order));
    }

    public function cancel(CancelOrderRequest $request, Order $order)
    {
        $this->authorize('cancel', $order);
        \DB::transaction(function () use ($request, &$order) {
            $user = auth()->user();
            $newStatus = $user->type === UserType::Customer ? OrderStatusEnum::CanceledByCustomer : OrderStatusEnum::CanceledByAdmin;
            $order->update([
                'status' => $newStatus,
                'cancellation_reason' => $request->validated('cancellation_reason'),
            ]);
            if ($saleList = $order->saleList) {
                $saleList->saleItems->each(function (SaleItem $saleItem) {
                    ProductStoreService::RefundSaleIntoStore($saleItem->product, -$saleItem->net_count, $saleItem, $saleItem->productUnit);
                    $saleItem->delete();
                });
                $saleList->serviceItems()->delete();
                $saleList->delete();

            } else {
                $order->orderItems()->each(function (OrderItem $orderItem) {
                    ProductStoreService::RefundSaleIntoStore($orderItem->product, -$orderItem->count, $orderItem, $orderItem->productUnit);
                });
            }
            if ($newStatus === OrderStatusEnum::CanceledByAdmin) {
                $order->customer->user?->notify(__('Order canceled'), __('Your order is canceled'), NotificationType::OrderCanceled, $order->id);
            } else {
                User::query()->whereHas('permissions', fn($q) => $q->whereName(PermissionEnum::VIEW_CUSTOMERS_ORDERS))
                    ->get()
                    ->each(fn(User $user) => $user->notify(__('Order canceled'), __('Order from customer :customer has been canceled', ['customer' => $order->customer->name]), NotificationType::OrderCanceled, $order->id));
                $order->driver?->user?->notify(__('Order canceled'), __('Order from customer :customer has been canceled', ['customer' => $order->customer->name]), NotificationType::OrderCanceled, $order->id);
            }
        });
        return ApiResponse::success(OrderResource::make($order));
    }


    public function finish(Order $order)
    {
        $this->authorize('finish', $order);
        if (!$order->saleList?->is_confirmed) {
            throw ValidationException::withMessages([__('Sale list must be confirmed')]);
        }
        $order->update([
            'status' => OrderStatusEnum::Finished
        ]);

        if (auth()->user()->type === UserType::Driver) {
            User::query()->whereHas('permissions', fn($q) => $q->whereName(PermissionEnum::VIEW_CUSTOMERS_ORDERS))
                ->each(fn(User $user) => $user->notify(__('Order finished'), __('Order from customer :customer is finished', ['customer' => $order->customer->name]), NotificationType::OrderFinished));
        } else {
            $order->driver?->user->notify(__('Order finished'), __('Order from customer :customer is finished', ['customer' => $order->customer->name]), NotificationType::OrderFinished);
        }
        $order->customer->user?->notify(__('Order finished'), __('Your order is finished'), NotificationType::OrderFinished);

        return ApiResponse::success(OrderResource::make($order));
    }
}
