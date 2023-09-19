<?php
declare(strict_types=1);

use App\Http\Controllers\BillController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\CarModelController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CarTypeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DiagnosisController;
use App\Http\Controllers\GlobalOptionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductLocationController;
use App\Http\Controllers\ProductTransactionController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\PurchaseListController;
use App\Http\Controllers\SaleItemController;
use App\Http\Controllers\SaleListController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceItemController;
use App\Http\Controllers\StockholderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MechanicController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\StockholderWithdrawController;
use App\Http\Middleware\OptionalAuth;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'guest'], function () {

    Route::POST('/login', [UserController::class, 'login']);
    Route::POST('/register-customer', [UserController::class, 'registerCustomer']);

});

Route::group(['middleware' => [OptionalAuth::class]], function () {
    Route::apiResource('products', ProductController::class)->only('index', 'show');
    Route::apiResource('categories', CategoryController::class)->only('index', 'show');
});

Route::group(['middleware' => ['auth:sanctum']], function () {

    require base_path('routes/urls/statistics.php');

    //User
    Route::apiResource('users', UserController::class)->only(['index', 'show', 'update']);

    Route::POST('/logout', [UserController::class, 'logout']);
    Route::GET('/current-user', [UserController::class, 'showCurrent']);
    Route::POST('/user/option', [UserController::class, 'setOption']);
    Route::GET('user/option/{option}', [UserController::class, 'getOption']);

    Route::POST('/add-user', [UserController::class, 'addUser']);
    Route::POST('/give-permission-to/{user}', [UserController::class, 'givePermissionTo']);
    Route::get('/get-user-permissions/{user}', [UserController::class, 'getUserPermissions']);
    Route::get('/get-all-permissions', [UserController::class, 'getAllPermissions']);


    Route::PUT('/profile', [ProfileController::class, 'update']);


    //Customers
    Route::get('customers/my_records', [CustomerController::class, 'records']);

    //Other Resources
    Route::apiResources([
        'product_locations' => ProductLocationController::class,
        'product_units' => ProductUnitController::class,
        'stockholders' => StockholderController::class,
        'stockholderwithdrawals' => StockholderWithdrawController::class,
        'expenses' => ExpenseController::class,
        'brands' => BrandController::class,
        'colors' => ColorController::class,
        'car_models' => CarModelController::class,
        'car_types' => CarTypeController::class,
        'diagnoses' => DiagnosisController::class,
        'services' => ServiceController::class,
        'drivers' => DriverController::class,
        'cars' => CarController::class,
        'customers' => CustomerController::class,
        'suppliers' => SupplierController::class,
        'mechanics' => MechanicController::class,
    ]);

    //Products
    Route::apiResource('payments', PaymentController::class)->only('index', 'show');
    Route::apiResource('categories', CategoryController::class)->only('store', 'update', 'destroy');
    Route::apiResource('products', ProductController::class)->only('store', 'update', 'destroy');

    Route::apiResource('products_transactions', ProductTransactionController::class)->only('show', 'index');


    //Sale Lists
    Route::post('sale_lists/{sale_list}/confirm', [SaleListController::class, 'confirm']);
    Route::post('sale_lists/{sale_list}/un_confirm', [SaleListController::class, 'unConfirm']);

    Route::apiResource('sale_items', SaleItemController::class);
    Route::apiResource('service_items', ServiceItemController::class);
    Route::apiResource('sale_lists', SaleListController::class);


    // Purchase lists
    Route::post('purchase_lists/{purchase_list}/confirm', [PurchaseListController::class, 'confirm']);
    Route::post('purchase_lists/{purchase_list}/un_confirm', [PurchaseListController::class, 'unConfirm']);
    Route::apiResource('purchase_lists', PurchaseListController::class);
    Route::apiResource('purchase_items', PurchaseItemController::class);


    //Bills
    Route::get('bills/code/{code}', [BillController::class, 'showByCode']);
    Route::post('bills/pay/{bill}', [BillController::class, 'pay']);
    Route::apiResource('bills', BillController::class)->only(['show', 'index']);

    //Notifications
    Route::apiResource('notifications', NotificationController::class)->only('index', 'store', 'show');
    Route::get('notifications-unseen-count', [NotificationController::class, 'unseenCount']);

    // Carts
    Route::get('carts/show-current', [CartController::class, 'showCurrent']);
    Route::put('carts/update-current', [CartController::class, 'updateCurrent']);
    Route::get('carts/user/{user}', [CartController::class, 'getCartByUser']);
    Route::post('carts/confirm-current', [CartController::class, 'confirmCurrent']);

    Route::post('cart-item', [CartItemController::class, 'store']);
    Route::put('cart-item/{cart_item}', [CartItemController::class, 'update']);
    Route::delete('cart-item/{cart_item}', [CartItemController::class, 'destroy']);

    Route::apiResource('cart_items', CartItemController::class)->only(['store', 'update', 'destroy']);

    //orders
    Route::apiResource('orders', OrderController::class)->only('index', 'show');
    Route::put('orders/{order}/confirm', [OrderController::class, 'confirm']);
    Route::put('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::put('orders/{order}/finish', [OrderController::class, 'finish']);

    //Others
    Route::apiResource('global_options', GlobalOptionController::class)->only(['index', 'update', 'show']);


});

Route::fallback(function () {
    abort(404, 'API resource not found');
});


/*
Just add ?XDEBUG_SESSION_START=filter_string at the end of the url, for eg:
    https://new-supplier.local/api/login?XDEBUG_SESSION_START=PHPSTORM*/
