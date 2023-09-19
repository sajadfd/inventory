<?php

namespace App\Http\Controllers;

use Alkoumi\LaravelArabicNumbers\Numbers;
use App\Enums\ExpenseSource;
use App\Enums\ProductTransactionEnum;
use App\Enums\SaleType;
use App\Http\ApiResponse;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InitialStore;
use App\Models\Product;
use App\Models\ProductTransaction;
use App\Models\PurchaseItem;
use App\Models\PurchaseList;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\ServiceItem;
use App\Models\Stockholder;
use App\Models\Supplier;
use App\Services\GeneratePDFService;
use App\Services\StatisticsUtilitiesService;
use Illuminate\Database\Query\Builder;

class StatisticsController extends Controller
{
    public function earningsStats()
    {
        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;

        $reportTitle = __('Earnings Statistics');

        $parametersInfo = [
            'columns' => [
                'parameter' => [
                    'title' => __('Parameter'),
                    'type' => 'text',
                ],
                'value_formatted' => [
                    'title' => __('Value'),
                    'type' => 'text',
                ],
                'value_as_text' => [
                    'title' => __('Value as Text'),
                    'type' => 'text',
                ],
            ],
            'sort_by' => request('sort_by', ''),
            'sort_direction' => request('sort_direction', 'desc')
        ];

        $purchaseListQuery = PurchaseList::query()->where('is_confirmed', true)->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            ->cursor();

        $saleListQuery = SaleList::query()->where('is_confirmed', true)->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            ->cursor();

        $inventorySaleListQuery = $saleListQuery->where('type', SaleType::InventorySale);
        $storeSaleListQuery = $saleListQuery->where('type', SaleType::StoreSale);

        $saleItemsQuery = SaleItem::query()
            ->withWhereHas(
                'saleList',
                fn($q) => $q
                    ->where('is_confirmed', true)
                    ->when($startDate, fn($q2) => $q2->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            )
            ->cursor();

        $serviceItemsQuery = ServiceItem::query()
            ->withWhereHas(
                'saleList',
                fn($q) => $q
                    ->where('is_confirmed', true)
                    ->when($startDate, fn($q2) => $q2->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            )
            ->cursor();

        $stockholdersQuery = Stockholder::query()
            //->where('is_active', true)
            ->cursor();
        $expensesQuery = Expense::query()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            //->where('is_active', true)
            ->cursor();

        $records = [
            [
                'name' => $name = 'purchase_lists_count',
                'parameter' => __($name),
                'value' => $value = $purchaseListQuery->count(),
                'value_formatted' => $value,
                'value_as_text' => Numbers::TafqeetNumber($value)
            ],
            [
                'name' => $name = 'purchase_lists_total_price_usd',
                'parameter' => __($name),
                'value' => $value = $purchaseListQuery->sum('total_price'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(number_format($value, 2, '.', ''), 'usd')
            ],
            [
                'name' => $name = 'inventory_sale_lists_count',
                'parameter' => __($name),
                'value' => $value = $inventorySaleListQuery->count(),
                'value_formatted' => $value,
                'value_as_text' => Numbers::TafqeetNumber($value)
            ],
            [
                'name' => $name = 'store_sale_lists_count',
                'parameter' => __($name),
                'value' => $value = $storeSaleListQuery->count(),
                'value_formatted' => $value,
                'value_as_text' => Numbers::TafqeetNumber($value)
            ],
            [
                'name' => $name = 'service_items_total_price_iqd',
                'parameter' => __($name),
                'value' => $value = $servicesPrice = $serviceItemsQuery->sum('total_price'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
            ],
            [
                'name' => $name = 'inventory_expenses_price_iqd',
                'parameter' => __($name),
                'value' => $value = $inventoryExpensesPrice = $expensesQuery->where('source', ExpenseSource::InventoryExpense)->sum('price_in_iqd'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
            ],
            [
                'name' => $name = 'sale_items_total_price_iqd',
                'parameter' => __($name),
                'value' => $value = $saleItemsQuery->sum('total_price'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
            ],
            [
                'name' => $name = 'sale_items_purchase_price_iqd',
                'parameter' => __($name),
                'value' => $value = $salesPurchasePrice = $saleItemsQuery->sum('purchase_price_in_iqd'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
            ],
            [
                'name' => $name = 'sale_items_purchase_price_usd',
                'parameter' => __($name),
                'value' => $value = $saleItemsQuery->sum('purchase_price_in_usd'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(number_format($value, 2, '.', ''), 'usd')
            ],
            [
                'name' => $name = 'products_earn_price_iqd',
                'parameter' => __($name),
                'value' => $value = $salesEarnPrice = $saleItemsQuery->sum('earn_price_in_iqd'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
            ],
            [
                'name' => $name = 'products_earn_price_usd',
                'parameter' => __($name),
                'value' => $value = $saleItemsQuery->sum('earn_price_in_usd'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(number_format($value, 2, '.', ''), 'usd')
            ],
            [
                'name' => $name = 'sale_items_earn_percent',
                'parameter' => __($name),
                'value' => $value = $salesPurchasePrice ? round(($salesEarnPrice / $salesPurchasePrice) * 100, 2) : 0,
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetNumber(number_format($value, 2, '.', '')) . ' ' . __('in percent')
            ],
            [
                'name' => $name = 'store_expenses_price_iqd',
                'parameter' => __($name),
                'value' => $value = $storeExpensesPrice = $expensesQuery->where('source', ExpenseSource::StoreExpense)->sum('price_in_iqd'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
            ],
            [
                'name' => $name = 'total_earn_price_iqd',
                'parameter' => __($name),
                'value' => $value = $totalEarn = $servicesPrice + $salesEarnPrice - $storeExpensesPrice - $inventoryExpensesPrice,
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
            ],
            [
                'name' => $name = 'store_stocks',
                'parameter' => __($name),
                'value' => $value = $storeStocksCount = $stockholdersQuery->sum('store_stocks'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetNumber($value),
            ],
            [
                'name' => $name = 'inventory_stocks',
                'parameter' => __($name),
                'value' => $value = $inventoryStocksCount = $stockholdersQuery->sum('inventory_stocks'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetNumber($value),
            ],
            [
                'name' => $name = 'total_stocks',
                'parameter' => __($name),
                'value' => $value = $stocksCount = $stockholdersQuery->sum('store_stocks') + $stockholdersQuery->sum('inventory_stocks'),
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetNumber($value),
            ],
            [
                'name' => $name = 'stock_earn_price_iqd',
                'parameter' => __($name),
                'value' => $value = $stockEarnPrice = $stocksCount ? ($totalEarn / $stocksCount) : 0,
                'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
            ],
            ...$stockholdersQuery->map(function (Stockholder $stockholder) use ($stockEarnPrice) {
                return [
                    'name' => $name = 'stockholder_' . $stockholder->id . '_earn_price_iqd',
                    'parameter' => __('Stockholder :name Earn Price', ["name" => $stockholder->name]),
                    'value' => $value = $stockEarnPrice * $stockholder->total_stocks,
                    'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                    'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
                ];
            })
        ];


        return $this->recordsStatisticsResponse($records, $parametersInfo, $reportTitle, groupAble: false);
    }

    public function inventoryEarningsStats()
    {
        $startDate = request()->input('start-date');
        $endDate = request()->input('end-date', $startDate);
        $reportTitle = __('Earnings Statistics');

        $parametersInfo = [
            'columns' => [
                'parameter' => [
                    'title' => __('Parameter'),
                    'type' => 'text',
                ],
                'value_formatted' => [
                    'title' => __('Value'),
                    'type' => 'text',
                ],
                'value_as_text' => [
                    'title' => __('Value as Text'),
                    'type' => 'text',
                ],
            ],
            'sort_by' => request('sort_by', ''),
            'sort_direction' => request('sort_direction', 'desc')
        ];
        $serviceItemsQuery = ServiceItem::query()
            ->withWhereHas(
                'saleList',
                fn($q) => $q
                    ->where('is_confirmed', true)
                    ->when($startDate, fn($q2) => $q2->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            )
            ->cursor();

        $stockholdersQuery = Stockholder::query()->cursor();
        $totalInventoryExpenses = Expense::query()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            ->where('source', 'inventory_expense')->sum('price');

        $saleItemsQuery = SaleItem::query()
            ->withWhereHas(
                'saleList',
                fn($q) => $q
                    ->where('is_confirmed', true)
                    ->when($startDate, fn($q2) => $q2->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            )->cursor();

        $inventorySaleItemsQuery = $saleItemsQuery->filter(function ($saleItem) {
            return $saleItem->product->source === 'inside';
        });

        $totalInventorySaleItemsPrice = $inventorySaleItemsQuery->sum(function ($saleItem) {
            return $saleItem->product->sale_price;
        });

        $totalInventorySaleItemsEarnPrice = $inventorySaleItemsQuery->sum(function ($saleItem) {
            return $saleItem->earn_price_in_iqd;
        });

        $totalServiceItemsPrice = $serviceItemsQuery->sum('total_price');
        $totalIncome = $totalServiceItemsPrice + $totalInventorySaleItemsEarnPrice;
        $totalIncomeWith15Percent = $totalServiceItemsPrice + ($totalInventorySaleItemsEarnPrice * 0.15);

        $netIncome = $totalIncomeWith15Percent - $totalInventoryExpenses;

        $totalInventoryStocks = $stockholdersQuery->sum('inventory_stocks');
        $earningsPerStock = $netIncome / $totalInventoryStocks;
        $records = [
            [
                'name' => $name = 'service_items_total_price_iqd',
                'parameter' => __($name),
                'value' => $totalServiceItemsPrice,
                'value_formatted' => StatisticsUtilitiesService::formatValue('service_items_total_price_iqd', $totalServiceItemsPrice),
                'value_as_text' => Numbers::TafqeetMoney(round($totalServiceItemsPrice), 'iqd')
            ],
            [
                'name' => $name = 'inventory_sale_items_total_price_iqd',
                'parameter' => __($name),
                'value' => $totalInventorySaleItemsPrice,
                'value_formatted' => StatisticsUtilitiesService::formatValue('inventory_sale_items_total_price_iqd', $totalInventorySaleItemsPrice),
                'value_as_text' => Numbers::TafqeetMoney(round($totalInventorySaleItemsPrice), 'iqd')
            ],
            [
                'name' => $name = 'inventory_sale_items_earn_price_iqd',
                'parameter' => __($name),
                'value' => $totalInventorySaleItemsEarnPrice,
                'value_formatted' => StatisticsUtilitiesService::formatValue('inventory_sale_items_earn_price_iqd', $totalInventorySaleItemsEarnPrice),
                'value_as_text' => Numbers::TafqeetMoney(round($totalInventorySaleItemsEarnPrice), 'iqd')
            ],
            [
                'name' => $name = 'total_income_price_iqd',
                'parameter' => __($name),
                'value' => $totalIncome,
                'value_formatted' => StatisticsUtilitiesService::formatValue('total_income_price_iqd', $totalIncome),
                'value_as_text' => Numbers::TafqeetMoney(round($totalIncome), 'iqd')
            ],
            [
                'name' => $name = 'total_income_with_15_percent',
                'parameter' => __($name),
                'value' => $totalIncomeWith15Percent,
                'value_formatted' => StatisticsUtilitiesService::formatValue('total_income_with_15_percent', $totalIncomeWith15Percent),
                'value_as_text' => Numbers::TafqeetMoney(round($totalIncomeWith15Percent), 'iqd')
            ],
            [
                'name' => $name = 'inventory_expenses_price_iqd',
                'parameter' => __($name),
                'value' => $totalInventoryExpenses,
                'value_formatted' => StatisticsUtilitiesService::formatValue('inventory_expenses_price_iqd', $totalInventoryExpenses),
                'value_as_text' => Numbers::TafqeetMoney(round($totalInventoryExpenses), 'iqd')
            ],
            [
                'name' => $name = 'net_income_price_iqd',
                'parameter' => __($name),
                'value' => $netIncome,
                'value_formatted' => StatisticsUtilitiesService::formatValue('net_income_price_iqd', $netIncome),
                'value_as_text' => Numbers::TafqeetMoney(round($netIncome), 'iqd')
            ],
            [
                'name' => $name = 'total_inventory_stocks_count',
                'parameter' => __($name),
                'value' => $totalInventoryStocks,
                'value_formatted' => StatisticsUtilitiesService::formatValue('total_inventory_stocks_count', $totalInventoryStocks),
                'value_as_text' => Numbers::TafqeetNumber($totalInventoryStocks)
            ],
            [
                'name' => $name = 'earnings_per_stock_price_iqd',
                'parameter' => __($name),
                'value' => $earningsPerStock,
                'value_formatted' => StatisticsUtilitiesService::formatValue('earnings_per_stock_price_iqd', $earningsPerStock),
                'value_as_text' => Numbers::TafqeetMoney(round($earningsPerStock), 'iqd')
            ],
            ...$stockholdersQuery->map(function (Stockholder $stockholder) use ($earningsPerStock) {
                return [
                    'name' => $name = 'stockholder_' . $stockholder->id . '_earn_price_iqd',
                    'parameter' => __('Stockholder :name Earn Price', ["name" => $stockholder->name]),
                    'value' => $value = $earningsPerStock * $stockholder->inventory_stocks,
                    'value_formatted' => StatisticsUtilitiesService::formatValue($name, $value),
                    'value_as_text' => Numbers::TafqeetMoney(round($value), 'iqd')
                ];
            })
        ];


        return $this->recordsStatisticsResponse($records, $parametersInfo, $reportTitle, groupAble: false);
    }

    public function carsStats()
    {
        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;
        $product = Product::find(request('product_id'));

        $reportTitle = __('Cars Statistics');

        $parametersInfo = [
            'columns' => [
                'type' => [
                    'title' => __('Type'),
                    'type' => 'text',
                ],
                'model' => [
                    'title' => __('Model'),
                    'type' => 'text',
                ],
                'model_year' => [
                    'title' => __('Year'),
                    'type' => 'text',
                ],
                'color' => [
                    'title' => __('Color'),
                    'type' => 'text',
                ],
                'customer' => [
                    'title' => __('Customer'),
                    'type' => 'text',
                ],
                'plate_number' => [
                    'title' => __('Plate Number'),
                    'type' => 'text',
                ],
                'lists_inventory_type_count' => [
                    'title' => __('Inventory Lists'),
                    'type' => 'number',
                ],
                'total_services_price_iqd' => [
                    'title' => __('Services Costs'),
                    'type' => 'currency',
                    'currency' => 'iqd'
                ],
            ],
            'sort_by' => request('sort_by', 'total_services_price_iqd'),
            'sort_direction' => request('sort_direction', 'desc')
        ];
        $carsQuery = Car::query()->where('is_active', true)
            ->withWhereHas('saleLists', function ($query) use ($startDate, $endDate) {
                $query->where('is_confirmed', true)
                    ->when($startDate, function ($query) use ($endDate, $startDate) {
                        $query->whereDate('date', '>=', $startDate)
                            ->whereDate('date', '<=', $endDate);
                    });
            })
            ->with('color', 'carType', 'carModel', 'customer');

        $cars = $carsQuery->cursor();
        $records = $cars->sortByDesc(fn($car) => $car->saleLists->sum('service_items_total_price'))->reduce(function ($carry, Car $car) {
            $carry[] = [
                'type' => $car->carType->name,
                'model' => $car->carModel->name,
                'model_year' => $car->model_year,
                'color' => $car->color->name,
                'customer' => $car->customer->name,
                'plate_number' => $car->plate_number,
                'lists_inventory_type_count' => $car->saleLists->count(),
                'total_services_price_iqd' => $car->saleLists->sum('service_items_total_price'),
            ];
            return $carry;
        }, []);

        return $this->recordsStatisticsResponse($records, $parametersInfo, $reportTitle, paperOrientation: 'landscape', groupAble: false);
    }

    public function servicesStats()
    {
        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;
        $product = Product::find(request('product_id'));

        $reportTitle = __('Services Statistics');

        $parametersInfo = [
            'columns' => [
                'service' => [
                    'title' => __('Service'),
                    'type' => 'text',
                ],
                'count' => [
                    'title' => __('Count'),
                    'type' => 'number',
                ],
                'unit_average_price_iqd' => [
                    'title' => __('Average Service Price'),
                    'type' => 'currency',
                    'currency' => 'iqd'
                ],
                'total_price_iqd' => [
                    'title' => __('Total Price'),
                    'type' => 'currency',
                    'currency' => 'iqd'
                ],
            ],
            'sort_by' => request('sort_by', 'total_price_iqd'),
            'sort_direction' => request('sort_direction', 'desc')
        ];
        $serviceItemsQuery = ServiceItem::query()
            ->withWhereHas('saleList', function ($query) use ($startDate, $endDate) {
                $query->where('is_confirmed', true)
                    ->when($startDate, function (\Illuminate\Database\Eloquent\Builder $query) use ($endDate, $startDate) {
                        $query->whereDate('date', '>=', $startDate)
                            ->whereDate('date', '<=', $endDate);
                    });
            })
            ->with('service', 'customer', 'car', 'car.carType', 'car.carModel', 'car.color');

        $serviceItems = $serviceItemsQuery->cursor();
        $records = $serviceItems->sortByDesc('total_price')->groupBy('service_id')
            ->reduce(function ($carry, $serviceItems, $serviceId) {
                $carry[] = [
                    'service' => $serviceName = $serviceItems[0]->service->name,
                    'count' => $serviceItems->sum('count'),
                    'unit_average_price_iqd' => $serviceItems->avg('price'),
                    'total_price_iqd' => $serviceItems->sum('total_price'),
                ];
                return $carry;
            }, []);

        return $this->recordsStatisticsResponse($records, $parametersInfo, $reportTitle, paperOrientation: 'landscape', groupAble: false);
    }

    public function purchaseStats()
    {
        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;

        $purchaseListQuery = PurchaseList::query()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            ->with(['purchaseItems', 'bill', 'billPayments'])->withCount('purchaseItems');

        $purchaseLists = $purchaseListQuery->get()->sortByDesc('total_price');
        $confirmedPurchaseLists = $purchaseLists->where('is_confirmed', true);
        $unConfirmedPurchaseLists = $purchaseLists->where('is_confirmed', false);

        $stats = [
            'lists_count' => $purchaseLists->count(),
            'lists_items_count' => $purchaseLists->sum->purchase_items_count,
            'lists_items_pieces_count' => $purchaseLists->sum->total_pieces,
            'lists_total_price_usd' => $purchaseLists->sum->total_price,

            'confirmed_lists_count' => $confirmedPurchaseLists->count(),
            'confirmed_lists_items_count' => $confirmedPurchaseLists->sum->purchase_items_count,
            'confirmed_lists_items_pieces_count' => $confirmedPurchaseLists->sum->total_pieces,
            'confirmed_lists_total_price_usd' => $confirmedTotalPrice = $confirmedPurchaseLists->sum->total_price,
            'confirmed_lists_payed_price_usd' => $confirmedPayedPrice = $confirmedPurchaseLists->sum(fn($p) => $p->billPayments->sum('price')),
            'confirmed_lists_remaining_price_usd' => $confirmedPurchaseLists->sum(fn($p) => $p->bill?->remaining_price),
            'confirmed_lists_payments_percent' => !$confirmedTotalPrice ? 0 : ($confirmedPayedPrice / $confirmedTotalPrice) * 100,

            'un_confirmed_lists_count' => $unConfirmedPurchaseLists->count(),
            'un_confirmed_lists_items_count' => $unConfirmedPurchaseLists->sum->purchase_items_count,
            'un_confirmed_lists_items_pieces_count' => $unConfirmedPurchaseLists->sum->total_pieces,
            'un_confirmed_lists_total_price_usd' => $unConfirmedPurchaseLists->sum->total_price,
        ];

        $parametersInfo = [
            'columns' => [
                'lists_count' => [
                    'title' => __('Lists Count'),
                    'type' => 'number'
                ],
                'lists_items_count' => [
                    'title' => __('Lists Items Count'),
                    'type' => 'number'
                ],
                'lists_items_pieces_count' => [
                    'title' => __('lists_items_pieces_count'),
                    'type' => 'number'
                ],
                'lists_total_price_usd' => [
                    'title' => __('Lists Total Price') . '  -',
                    'type' => 'currency',
                    'currency' => 'usd',
                ],
                'lists_payed_price_usd' => [
                    'title' => __('Lists Payed Price'),
                    'type' => 'currency',
                    'currency' => 'usd',
                ],
                'lists_remaining_price_usd' => [
                    'title' => __('Lists Remaining Price'),
                    'type' => 'currency',
                    'currency' => 'usd',
                ],
                'lists_payments_percent' => [
                    'title' => __('Lists Payment Percent'),
                    'type' => 'percent',
                ],
            ]
        ];

        $reportTitle = 'Purchases Statistics';
        return $this->listStatisticsResponse($stats, $reportTitle, parametersInfo: $parametersInfo);
    }

    public function salesStats()
    {

        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;

        $saleListQuery = SaleList::query()
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate))
            ->with(['saleItems', 'serviceItems', 'bill', 'billPayments'])
            ->withCount(['serviceItems']);

        $saleLists = $saleListQuery->get();
        $confirmedSaleLists = $saleLists->where('is_confirmed', true);
        $unConfirmedSaleLists = $saleLists->where('is_confirmed', false);

        $stats = [
            'lists_count' => $saleLists->count(),
            'lists_store_type_count' => $saleLists->where('type', SaleType::StoreSale)->count(),
            'lists_inventory_type_count' => $saleLists->where('type', SaleType::InventorySale)->count(),
            'lists_sale_items_count' => $saleLists->sum->sale_items_count,
            'lists_sale_items_pieces_count' => $saleLists->sum->sale_items_total_pieces,
            'lists_sale_items_total_price_iqd' => $saleLists->sum->sale_items_total_price,
            'lists_service_items_count' => $saleLists->sum->service_items_count,
            'lists_service_items_pieces_count' => $saleLists->sum->service_items_total_pieces,
            'lists_service_items_total_price_iqd' => $saleLists->sum->service_items_total_price,
            'lists_total_price_iqd' => $saleLists->sum->total_price,


            'confirmed_lists_count' => $confirmedSaleLists->count(),
            'confirmed_lists_store_type_count' => $confirmedSaleLists->where('type', SaleType::StoreSale)->count(),
            'confirmed_lists_inventory_type_count' => $confirmedSaleLists->where('type', SaleType::InventorySale)->count(),
            'confirmed_lists_sale_items_count' => $confirmedSaleLists->sum->sale_items_count,
            'confirmed_lists_sale_items_pieces_count' => $confirmedSaleLists->sum->sale_items_total_pieces,
            'confirmed_lists_sale_items_total_price_iqd' => $confirmedSaleLists->sum->sale_items_total_price,
            'confirmed_lists_service_items_count' => $confirmedSaleLists->sum->service_items_count,
            'confirmed_lists_service_items_pieces_count' => $confirmedSaleLists->sum->service_items_total_pieces,
            'confirmed_lists_service_items_total_price_iqd' => $confirmedSaleLists->sum->service_items_total_price,
            'confirmed_lists_total_price_iqd' => $confirmedTotalPrice = $confirmedSaleLists->sum->total_price,
            'confirmed_lists_payed_price_iqd' => $confirmedPayedPrice = $confirmedSaleLists->sum(fn($p) => $p->billPayments->sum('price')),
            'confirmed_lists_remaining_price_iqd' => $confirmedSaleLists->sum(fn($p) => $p->bill?->remaining_price),
            'confirmed_lists_payments_percent' => ($confirmedPayedPrice / $confirmedTotalPrice) * 100,


            'un_confirmed_lists_count' => $unConfirmedSaleLists->count(),
            'un_confirmed_lists_store_type_count' => $unConfirmedSaleLists->where('type', SaleType::StoreSale)->count(),
            'un_confirmed_lists_inventory_type_count' => $unConfirmedSaleLists->where('type', SaleType::InventorySale)->count(),
            'un_confirmed_lists_sale_items_count' => $unConfirmedSaleLists->sum->sale_items_count,
            'un_confirmed_lists_sale_items_pieces_count' => $unConfirmedSaleLists->sum->sale_items_total_pieces,
            'un_confirmed_lists_sale_items_total_price_iqd' => $unConfirmedSaleLists->sum->sale_items_total_price,
            'un_confirmed_lists_service_items_count' => $unConfirmedSaleLists->sum->service_items_count,
            'un_confirmed_lists_service_items_pieces_count' => $unConfirmedSaleLists->sum->service_items_total_pieces,
            'un_confirmed_lists_service_items_total_price_iqd' => $unConfirmedSaleLists->sum->service_items_total_price,
            'un_confirmed_lists_total_price_iqd' => $unConfirmedSaleLists->sum->total_price,
        ];

        $parametersInfo = [
            'columns' => [
                'lists_count' => [
                    'title' => __('Lists Count'),
                    'type' => 'number'
                ],
                'lists_store_type_count' => [
                    'title' => __('lists_store_type_count'),
                    'type' => 'number'
                ],
                'lists_inventory_type_count' => [
                    'title' => __('lists_inventory_type_count'),
                    'type' => 'number'
                ],
                'lists_sale_items_count' => [
                    'title' => __('lists_sale_items_count'),
                    'type' => 'number'
                ],
                'lists_sale_items_pieces_count' => [
                    'title' => __('lists_sale_items_pieces_count'),
                    'type' => 'number'
                ],
                'lists_sale_items_total_price_iqd' => [
                    'title' => __('lists_sale_items_total_price'),
                    'type' => 'currency',
                    'currency' => 'iqd'
                ],
                'lists_service_items_count' => [
                    'title' => __('lists_service_items_count'),
                    'type' => 'number'
                ],
                'lists_service_items_pieces_count' => [
                    'title' => __('lists_service_items_pieces_count'),
                    'type' => 'number'
                ],
                'lists_service_items_total_price_iqd' => [
                    'title' => __('lists_service_items_total_price'),
                    'type' => 'currency',
                    'currency' => 'iqd'
                ],
                'lists_total_price_iqd' => [
                    'title' => __('Lists Total Price') . '  -',
                    'type' => 'currency',
                    'currency' => 'iqd',
                ],

                'lists_payed_price_iqd' => [
                    'title' => __('Lists Payed Price'),
                    'type' => 'currency',
                    'currency' => 'iqd',
                ],
                'lists_remaining_price_iqd' => [
                    'title' => __('Lists Remaining Price'),
                    'type' => 'currency',
                    'currency' => 'iqd',
                ],
                'lists_payments_percent' => [
                    'title' => __('Lists Payment Percent'),
                    'type' => 'percent',
                ],
            ]
        ];

        $reportTitle = 'Sales Statistics';
        return $this->listStatisticsResponse($stats, $reportTitle, parametersInfo: $parametersInfo);
    }

    public function suppliersStats()
    {
        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;

        $asGroups = request()->boolean('as-groups', true);
        $reportTitle = 'Suppliers Records';

        $suppliersQuery = Supplier::whereIsActive(true)
            ->when($supplier_id = request()->input('supplier_id'), fn($q) => $q->where('id', $supplier_id))
            ->with(['purchaseLists', 'bills', 'purchaseLists.purchaseItems', 'purchaseLists.bill', 'purchaseLists.bill.payments']);
        if ($startDate) {
            $suppliersQuery
                ->whereHas('purchaseLists', function ($q) use ($startDate, $endDate) {
                    $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate);
                });
        }
        $suppliers = $suppliersQuery->get();
        $parametersInfo = [
            'columns' => [
                'name' => [
                    'title' => __('Name'),
                    'type' => 'text',
                    'group_position' => 'start',
                ],
                'lists_count' => [
                    'title' => __('Lists Count'),
                    'type' => 'number'
                ],
                'lists_items_count' => [
                    'title' => __('Items Count'),
                    'type' => 'number'
                ],
                'lists_pieces_count' => [
                    'title' => __('Pieces Count'),
                    'type' => 'number'
                ],
                'lists_total_price_usd' => [
                    'title' => __('Total Price'),
                    'type' => 'currency',
                    'currency' => 'usd',
                ],
                'lists_payed_price_usd' => [
                    'title' => __('Payed Price'),
                    'type' => 'currency',
                    'currency' => 'usd',
                ],
                'lists_remaining_price_usd' => [
                    'title' => __('Remaining Price'),
                    'type' => 'currency',
                    'currency' => 'usd',
                ],
                'debts_price_usd' => [
                    'title' => __('Total Debts'),
                    'type' => 'currency',
                    'currency' => 'usd',
                    'group_position' => 'end',
                ],
            ],
            'sort_by' => request('sort_by', 'debts_price_usd'),
            'sort_direction' => request('sort_direction', 'desc')
        ];
        $records = $suppliers->sortByDesc('debts')->reduce(function (array $carry, Supplier $supplier) use ($asGroups) {
            $purchaseLists = $supplier->purchaseLists;
            $confirmedPurchaseLists = $purchaseLists->where('is_confirmed', true);
            $unConfirmedPurchaseLists = $purchaseLists->where('is_confirmed', false);
            $supplierStatistics = [];
            foreach (['total', 'confirmed', 'un_confirmed'] as $status) {
                $targetPurchaseLists = match ($status) {
                    'confirmed' => $confirmedPurchaseLists,
                    'un_confirmed' => $unConfirmedPurchaseLists,
                    default => $purchaseLists,
                };
                if (($cnt = $targetPurchaseLists->count()) || $status === 'total') {
                    $supplierStatistics[$status] = [
                            'name' => $supplier->name,
                            'lists_count' => $cnt,
                            'lists_items_count' => $targetPurchaseLists->sum(fn(PurchaseList $purchaseList) => $purchaseList->purchaseItems->count()),
                            'lists_pieces_count' => $targetPurchaseLists->sum(fn(PurchaseList $purchaseList) => $purchaseList->purchaseItems->sum('count')),
                            'lists_total_price_usd' => $targetPurchaseLists->sum->total_price,
                        ] + ($status === 'un_confirmed' ? [] : [
                            'lists_payed_price_usd' => $targetPurchaseLists->sum(fn(PurchaseList $purchaseList) => $purchaseList->bill?->payed_price),
                            'lists_remaining_price_usd' => $targetPurchaseLists->sum(fn(PurchaseList $purchaseList) => $purchaseList->bill?->remaining_price),
                        ])
                        + [
                            'debts_price_usd' => $supplier->debts,
                        ];
                }
            }
            return $this->flatterGroups($asGroups, $supplierStatistics, $carry);
        }, []);
        return $this->recordsStatisticsResponse($records, $parametersInfo, $reportTitle);
    }

    public function customersStats()
    {
        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;

        $parametersInfo = [
            'columns' => [
                'name' => [
                    'title' => __('Name'),
                    'type' => 'text',
                    'group_position' => 'start',
                ],
                'cars_count' => [
                    'title' => __('Cars'),
                    'type' => 'text',
                    'group_position' => 'start',
                ],
                /* 'lists_count' => [
                     'title' => __('Lists Count'),
                     'type' => 'number'
                 ],*/
                'lists_store_type_count' => [
                    'title' => __('Store Lists'),
                    'type' => 'number'
                ],
                'lists_inventory_type_count' => [
                    'title' => __('Inventory Lists'),
                    'type' => 'number'
                ],
                'lists_sale_items_count' => [
                    'title' => __('Products Count'),
                    'type' => 'number'
                ],
                'lists_sale_items_pieces_count' => [
                    'title' => __('Products Pieces'),
                    'type' => 'number'
                ],
                'lists_sale_items_total_price_iqd' => [
                    'title' => __('Products Price'),
                    'type' => 'currency',
                    'currency' => 'iqd',
                ],
                'lists_service_items_count' => [
                    'title' => __('Services Count'),
                    'type' => 'number'
                ],
                /* 'lists_service_items_pieces_count' => [
                     'title' => __('Services Pieces'),
                     'type' => 'number'
                 ],*/
                'lists_service_items_total_price_iqd' => [
                    'title' => __('Services Price'),
                    'type' => 'currency',
                    'currency' => 'iqd',
                ],
                'lists_total_price_iqd' => [
                    'title' => __('Total Prices'),
                    'type' => 'currency',
                    'currency' => 'iqd',
                ],
                'lists_payed_price_iqd' => [
                    'title' => __('Payed Price'),
                    'type' => 'currency',
                    'currency' => 'iqd',
                ],
                'lists_remaining_price_iqd' => [
                    'title' => __('Remaining'),
                    'type' => 'currency',
                    'currency' => 'iqd',
                ],
                'debts_price_iqd' => [
                    'title' => __('Total Debts'),
                    'type' => 'currency',
                    'currency' => 'iqd',
                    'group_position' => 'end',
                ],
            ],
            'sort_by' => request('sort_by', 'debts_price_iqd'),
            'sort_direction' => request('sort_direction', 'desc')
        ];

        $asGroups = request()->boolean('as-groups', true);
        $reportTitle = 'Customers Records';
        $customersQuery = Customer::whereIsActive(true)
            ->when($customer_id = request()->input('customer_id'), fn($q) => $q->where('id', $customer_id))
            ->whereHas('saleLists', function ($q) use ($startDate, $endDate) {
                if ($startDate) {
                    $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate);
                }
            })
            ->withCount(['cars'])
            ->with(['saleLists', 'saleLists.bill', 'saleLists.bill.payments']);

        if ($startDate) {
            $customersQuery
                ->whereHas('saleLists', function ($q) use ($startDate, $endDate) {
                    $q->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate);
                });
        }
        $customers = $customersQuery->cursor();

        $records = $customers->sortByDesc('debts')->reduce(function (array $carry, Customer $customer) use ($asGroups) {
            $saleLists = $customer->saleLists;
            $confirmedSaleLists = $saleLists->where('is_confirmed', true);
            $unConfirmedSaleLists = $saleLists->where('is_confirmed', false);
            $customerStatistics = [];
            foreach (['total', 'confirmed', 'un_confirmed'] as $status) {
                $targetSaleLists = match ($status) {
                    'confirmed' => $confirmedSaleLists,
                    'un_confirmed' => $unConfirmedSaleLists,
                    default => $saleLists,
                };
                if (($cnt = $targetSaleLists->count()) || $status === 'total') {
                    $storeSaleLists = $targetSaleLists->where('type', SaleType::StoreSale);
                    $inventorySaleLists = $targetSaleLists->where('type', SaleType::InventorySale);
                    $customerStatistics[$status] = [
                            'name' => $customer->name,
                            'cars_count' => $customer->cars_count,

                            //                            'lists_count' => $cnt,
                            'lists_store_type_count' => $storeSaleLists->count(),
                            'lists_inventory_type_count' => $inventorySaleLists->count(),

                            'lists_sale_items_count' => $targetSaleLists->sum(fn(SaleList $saleList) => $saleList->saleItems->count()),
                            'lists_sale_items_pieces_count' => $targetSaleLists->sum(fn(SaleList $saleList) => $saleList->saleItems->sum('net_count')),
                            'lists_sale_items_total_price_iqd' => $targetSaleLists->sum(fn(SaleList $saleList) => $saleList->saleItems->sum('total_price')),

                            'lists_service_items_count' => $targetSaleLists->sum(fn(SaleList $saleList) => $saleList->serviceItems->count()),
                            'lists_service_items_pieces_count' => $targetSaleLists->sum(fn(SaleList $saleList) => $saleList->serviceItems->sum('count')),
                            'lists_service_items_total_price_iqd' => $targetSaleLists->sum(fn(SaleList $saleList) => $saleList->serviceItems->sum('total_price')),

                            'lists_total_price_iqd' => $targetSaleLists->sum->total_price,
                            'debts_price_iqd' => $customer->debts,
                        ] + ($status === 'un_confirmed' ? [] : [
                            'lists_payed_price_iqd' => $targetSaleLists->sum(fn(SaleList $saleList) => $saleList->bill?->payed_price),
                            'lists_remaining_price_iqd' => $targetSaleLists->sum(fn(SaleList $saleList) => $saleList->bill?->remaining_price),
                        ]);
                }
            }
            return $this->flatterGroups($asGroups, $customerStatistics, $carry);
        }, []);

        return $this->recordsStatisticsResponse($records, $parametersInfo, $reportTitle, paperOrientation: 'landscape');
    }

    public function productsStats()
    {
        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;
        $parametersInfo = [
            'columns' => [
                'name' => [
                    'title' => __('Name'),
                    'type' => 'text',
                ],
                'category' => [
                    'title' => __('Category'),
                    'type' => 'text',
                ],
                'store' => [
                    'title' => __('Product Store'),
                    'type' => 'number',
                ],
                'latest_purchase_price_usd' => [
                    'title' => __('Latest Purchase Price'),
                    'type' => 'currency',
                    'currency' => 'usd',
                ],
                'sale_price_usd' => [
                    'title' => __('Current Sale Price'),
                    'type' => 'currency',
                    'currency' => 'usd',
                ],
                'created_at' => [
                    'title' => __('Creation Date'),
                    'type' => 'date',
                    'format' => 'Y-m-d h:mA',
                    'style' => 'direction:ltr;text-align:center',
                ],
            ],
            'sort_by' => request('sort_by', 'created_at'),
            'sort_direction' => request('sort_direction', 'desc')
        ];

        $reportTitle = 'Products Records';
        $productsQuery = Product::whereIsActive(true)
            ->when($startDate, function ($query) use ($startDate, $endDate) {
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('category_id')
            ->orderBy('store', 'desc')
            ->when($product_id = request()->input('product_id'), fn($q) => $q->where('id', $product_id))
            ->with(['category', 'initialStore', 'latestPurchaseItem']);

        $products = $productsQuery->cursor();

        function productStatsMapper(Product $product, &$carry = null)
        {
            $map = [
                'name' => $product->name,
                'store' => $product->store,
                'latest_purchase_price_usd' => $product->latest_purchase_price,
                'sale_price_usd' => $product->sale_price,
                'created_at' => $product->created_at,
            ];
            if ($carry !== null) {
                $carry[] = $map;
                return $carry;
            } else {
                return $map;
            }
        }

        $records = $products->reduce(fn($carry, $product) => productStatsMapper($product, $carry), []);
        return $this->recordsStatisticsResponse($records, $parametersInfo, $reportTitle, groupAble: false);
    }

    public function productTransactionsStats()
    {

        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;
        $product = Product::find(request('product_id'));

        $reportTitle = $product ? (__('Product Transactions') . ": " . $product->name) : (__('Products Transactions'));

        $parametersInfo = [
            'columns' => [
                'date' => [
                    'title' => __('Date'),
                    'type' => 'date',
                    'style' => 'direction:ltr;text-align:center',
                    'format' => 'Y-m-d h:mA'
                ],
                'type' => [
                    'title' => __('Type'),
                    'type' => 'text',
                    'text_style_column' => 'type_style',
                ],
                ...($product ? [] : [
                    'product_name' => [
                        'title' => __('Product'),
                        'type' => 'text',
                    ]
                ]),
                'count' => [
                    'title' => __('Amount'),
                    'type' => 'text',
                ],
                'supplier' => [
                    'title' => __('Supplier'),
                    'type' => 'text',
                    'text_style_column' => 'supplier_style',
                ],
                'single_purchase_price_usd' => [
                    'title' => __('Unit Purchase Price'),
                    'type' => 'currency',
                    'currency' => 'usd'
                ],
                'total_purchase_price_usd' => [
                    'title' => __('Total Purchase Price'),
                    'type' => 'currency',
                    'currency' => 'usd'
                ],
                'customer' => [
                    'title' => __('Customer'),
                    'type' => 'text',
                ],
                'single_sale_price_usd' => [
                    'title' => __('Unit Sale Price'),
                    'type' => 'currency',
                    'currency' => 'usd'
                ],
                'total_sale_price_usd' => [
                    'title' => __('Total Sale Price'),
                    'type' => 'currency',
                    'currency' => 'usd'
                ],
                'single_earn_price_usd' => [
                    'title' => __('Unit Earn Price'),
                    'type' => 'currency',
                    'currency' => 'usd'
                ],
                'total_earn_price_usd' => [
                    'title' => __('Total Earn Price'),
                    'type' => 'currency',
                    'currency' => 'usd'
                ],
                'earn_percent' => [
                    'title' => __('Earn Percent'),
                    'type' => 'percent',
                ],
            ],
            'sort_by' => request('sort_by', 'date'),
            'sort_direction' => request('sort_direction', 'desc')
        ];
        $transactionsQuery = ($product?->transactions() ?: ProductTransaction::query())
            ->when($startDate, function ($query) use ($endDate, $startDate) {
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->with('sourceable', 'sourceable.list', 'sourceable.list.person', 'targetable.list', 'targetable.list.person')
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        $transactions = $transactionsQuery->cursor();
        $records = $transactions->reduce(function ($carry, ProductTransaction $transaction) {
            $item = [
                'product_name' => __($transaction->product->name),
                'type' => __(match ($transaction->type) {
                    ProductTransactionEnum::Purchase => ProductTransactionEnum::Purchase->name,
                    ProductTransactionEnum::Sale => $transaction->targetable?->list?->type->name ?: ProductTransactionEnum::Sale,
                    default => $transaction->type->name
                }),
                'type_style' => 'font-weight:bold;color:' . match ($transaction->type) {
                        ProductTransactionEnum::Initial => 'darkorange',
                        ProductTransactionEnum::Purchase => 'blue',
                        ProductTransactionEnum::Sale => match ($transaction->targetable?->list->type) {
                            SaleType::StoreSale => 'green',
                            SaleType::InventorySale => 'purple',
                        },
                        default => 'black'
                    },
                'date' => $transaction->created_at,
                'count' => $count = $transaction->count,
                'single_purchase_price_usd' => $singlePurchasePrice = match ($transaction->type) {
                    ProductTransactionEnum::Initial => $transaction->targetable?->price,
                    ProductTransactionEnum::Purchase => $transaction->targetable?->price,
                    default => $transaction->sourceable?->price,
                },
                'total_purchase_price_usd' => round($singlePurchasePrice * $count, 2),
            ];
            if ($transaction->type === ProductTransactionEnum::Purchase && $transaction->targetable) {
                $item += [
                    'supplier' => $transaction->targetable->list->person->name
                ];
            }
            if ($transaction->type === ProductTransactionEnum::Sale && $transaction->targetable) {
                $item += [
                    'supplier' => match (get_class($transaction->sourceable)) {
                        PurchaseItem::class => $transaction->sourceable->list?->person->name,
                        InitialStore::class => '(' . __('Initial Store') . ')',
                    },
                    'supplier_style' => get_class($transaction->sourceable) === InitialStore::class ? 'font-style:italic;' : '',
                    'single_sale_price_usd' => $singleSalePrice = $transaction->targetable->price_in_usd,
                    'total_sale_price_usd' => round($singleSalePrice * $count, 2),
                    'single_earn_price_usd' => $earnPrice = $singleSalePrice - $singlePurchasePrice,
                    'total_earn_price_usd' => round($singleSalePrice * $count, 2),
                    'earn_percent' => $singlePurchasePrice ? round(($earnPrice / $singlePurchasePrice) * 100, 2) : 0,
                    'customer' => $transaction->targetable->list->person->name,
                ];
            }
            $carry[] = $item;
            return $carry;
        }, []);

        return $this->recordsStatisticsResponse($records, $parametersInfo, $reportTitle, paperOrientation: 'landscape', groupAble: false);
    }

    public function flatterGroups(bool $asGroups, array $customerStatistics, array $carry): array
    {
        if (!$asGroups) {
            $flattenedStats = [];
            foreach ($customerStatistics as $groupName => $values) {
                foreach ($values as $k => $value) {
                    $newKey = $groupName === 'total' ? $k : $groupName . '_' . $k;
                    $flattenedStats[$newKey] = $value;
                }
            }
            $customerStatistics = $flattenedStats;
        }
        $carry[] = $customerStatistics;
        return $carry;
    }

    public function listStatisticsResponse($stats, $reportTitle, $paperSize = 'A4', $parametersInfo = [])
    {
        $asPdf = request()->boolean('as-pdf', false);
        $asHTML = request()->boolean('as-html', false);
        $asGroups = request()->boolean('as-groups', true);
        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;

        $groupedStats = StatisticsUtilitiesService::groupStatisticsArray($stats, true);
        if ($asHTML || $asPdf) {
            $view = view('statistics.lists-stats', compact('startDate', 'endDate', 'groupedStats', 'paperSize', 'reportTitle', 'asPdf'));
        }

        if ($asPdf) {
            $pdf = GeneratePDFService::generate($view, $paperSize);
            return $pdf->download($reportTitle . '.pdf');
        } else if ($asHTML) {
            return $view;
        } else {
            $recordsStats = $asGroups ? StatisticsUtilitiesService::groupStatisticsArray($stats, false) : $stats;;
            return ApiResponse::success(['parametersInfo' => $parametersInfo, 'records' => $recordsStats, 'recordsFormatter' => $groupedStats,]);
        }
    }

    public function recordsStatisticsResponse($records, $parametersInfo, $reportTitle, $paperSize = 'A4', $paperOrientation = 'portrait', $groupAble = true)
    {
        $asPdf = request()->boolean('as-pdf', false);
        $asHTML = request()->boolean('as-html', false);
        $startDate = request()->date('start-date');
        $endDate = request()->date('end-date') ?: $startDate;
        $asGroups = request()->boolean('as-groups', true) || $asPdf || $asHTML;
        $recordsFormatted = StatisticsUtilitiesService::formatRecords($records, $groupAble);
        //        return  $recordsFormatted;
        if ($asHTML || $asPdf) {
            if ($asHTML || $asPdf) {
                $view = view('statistics.records-stats', compact('startDate', 'endDate', 'parametersInfo', 'records', 'recordsFormatted', 'paperSize', 'reportTitle', 'paperOrientation', 'asPdf', 'groupAble'));
            }
        }
        if ($asPdf) {
            $pdf = GeneratePDFService::generate($view, $paperSize, $paperOrientation);
            return $pdf->download($reportTitle . '.pdf');
        } else if ($asHTML) {
            return $view;
        } else {
            return ApiResponse::success(compact('parametersInfo', 'recordsFormatted', 'records'));
        }
    }
}
