<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\ApiResponse;
use App\Http\Resources\BillResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\SupplierResource;
use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Services\PaginatorService;
use App\Services\UploadImageService;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Customer::class, 'customer');
    }

    public function index()
    {
        return ApiResponse::success(PaginatorService::from(Customer::query(), CustomerResource::class));
    }

    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        (new UploadImageService)->saveAuto($data);

        $customer = Customer::query()->create($data);
        return ApiResponse::success(CustomerResource::make($customer));
    }

    public function show(Customer $customer)
    {
        if (request()->boolean('with_details')) {
            return $this->records($customer);
        }
        return ApiResponse::success(CustomerResource::make($customer));
    }


    public function update(StoreCustomerRequest $request, Customer $customer)
    {
        $data = $request->validated();
        (new UploadImageService)->deleteAndSaveAuto($data);

        $customer->update($data);
        if ($customer->user_id) {
            $customer->user?->profile?->update([
                'first_name' => $customer->name,
                'last_name' => '',
                'address' => $customer->address,
            ]);
            if ($customer->user?->phone) {
                $customer->update(['phone' => $customer->user->phone]);
            }
        }
        return ApiResponse::success(CustomerResource::make($customer));
    }

    public function destroy(Customer $customer)
    {
        if ($customer->saleLists()->exists()) {
            throw ValidationException::withMessages([__('This customer has orders, can not be deleted')]);
        } else if ($customer->user()->exists()) {
            throw ValidationException::withMessages([__('This customer profile belongs to user, can not be deleted')]);
        }
        return ApiResponse::success($customer->delete());
    }

    public function records(?Customer $customer)
    {
        if (auth()->user()?->type === UserType::Customer && auth()->user()->customer) {
            $customer = auth()->user()->customer;
        }else if(!$customer){
            return  ApiResponse::error(__('Not Allowed'),403);
        }

        $customer->load('bills');
        //Unnecessary query, for future optimization
        $bills = PaginatorService::from($customer->bills(), BillResource::class);
        $customer->append(['debts', 'bills_total_price', 'bills_total_count', 'bills_un_payed_count']);
        $customer->makeHidden('bills');
        return ApiResponse::success([
            'customer' => CustomerResource::make($customer),
            'bills' => $bills
        ]);
    }
}
