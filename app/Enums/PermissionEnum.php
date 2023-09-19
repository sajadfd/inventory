<?php
declare(strict_types=1);

namespace App\Enums;

use Modules\HR\Enums\HRPermissionEnum;
use ReflectionClass;

enum PermissionEnum
{
    const VIEW_USERS = 'view_users';

    const CREATE_USERS = 'create_users';
    const UPDATE_USERS = 'update_users';
    const UPDATE_PERMISSIONS = 'update_permissions';

    const CREATE_PRODUCTS = 'create_products';
    const UPDATE_PRODUCTS = 'update_products';
    const DELETE_PRODUCTS = 'delete_products';

    const CREATE_CATEGORIES = 'create_categories';
    const UPDATE_CATEGORIES = 'update_categories';
    const DELETE_CATEGORIES = 'delete_categories';

    const VIEW_SUPPLIERS = 'view_suppliers';
    const CREATE_SUPPLIERS = 'create_suppliers';
    const UPDATE_SUPPLIERS = 'update_suppliers';
    const DELETE_SUPPLIERS = 'delete_suppliers';

    const VIEW_PURCHASE_LISTS = 'view_purchase_lists';
    const CREATE_PURCHASE_LISTS = 'create_purchase_lists';
    const UPDATE_PURCHASE_LISTS = 'update_purchase_lists';
    const DELETE_PURCHASE_LISTS = 'delete_purchase_lists';
    const CONFIRM_PURCHASE_LISTS = 'confirm_purchase_lists';
    const UN_CONFIRM_PURCHASE_LISTS = 'un_confirm_purchase_lists';
    const AUTO_PAY_PURCHASE_LISTS = 'auto_pay_purchase_lists';

    const CREATE_PURCHASE_ITEMS = 'create_purchase_items';
    const UPDATE_PURCHASE_ITEMS = 'update_purchase_items';
    const DELETE_PURCHASE_ITEMS = 'delete_purchase_items';

    const VIEW_CUSTOMERS = 'view_customers';
    const CREATE_CUSTOMERS = 'create_customers';
    const UPDATE_CUSTOMERS = 'update_customers';
    const DELETE_CUSTOMERS = 'delete_customers';

    const VIEW_BRANDS = 'view_brands';
    const CREATE_BRANDS = 'create_brands';
    const UPDATE_BRANDS = 'update_brands';
    const DELETE_BRANDS = 'delete_brands';

    const VIEW_USER_CARTS = 'view_user_carts';
    const CREATE_USER_CARTS_ITEMS = 'create_user_carts_items';
    const UPDATE_USER_CARTS_ITEMS = 'update_user_carts_items';
    const DELETE_USER_CARTS_ITEMS = 'delete_user_carts_items';

    const VIEW_CARS = 'view_cars';
    const CREATE_CARS = 'create_cars';
    const UPDATE_CARS = 'update_cars';
    const DELETE_CARS = 'delete_cars';

    const VIEW_CAR_MODELS = 'view_car_models';
    const CREATE_CAR_MODELS = 'create_car_models';
    const UPDATE_CAR_MODELS = 'update_car_models';
    const DELETE_CAR_MODELS = 'delete_car_models';

    const VIEW_CAR_TYPES = 'view_car_types';
    const CREATE_CAR_TYPES = 'create_car_types';
    const UPDATE_CAR_TYPES = 'update_car_types';
    const DELETE_CAR_TYPES = 'delete_car_types';

    const VIEW_COLORS = 'view_colors';
    const CREATE_COLORS = 'create_colors';
    const UPDATE_COLORS = 'update_colors';
    const DELETE_COLORS = 'delete_colors';

    const VIEW_STOCKHOLDERS = 'view_stockholders';
    const CREATE_STOCKHOLDERS = 'create_stockholders';
    const UPDATE_STOCKHOLDERS = 'update_stockholders';
    const DELETE_STOCKHOLDERS = 'delete_stockholders';

    const VIEW_SERVICES = 'view_services';
    const CREATE_SERVICES = 'create_services';
    const UPDATE_SERVICES = 'update_services';
    const DELETE_SERVICES = 'delete_services';

    const VIEW_MECHANIC = 'view_mechanics';
    const CREATE_MECHANIC = 'create_mechanic';
    const UPDATE_MECHANIC = 'update_mechanic';
    const DELETE_MECHANIC = 'delete_mechanic';

    const VIEW_SALE_LISTS = 'view_sale_lists';
    const CREATE_SALE_LISTS = 'create_sale_lists';
    const UPDATE_SALE_LISTS = 'update_sale_lists';
    const DELETE_SALE_LISTS = 'delete_sale_lists';
    const CONFIRM_SALE_LISTS = 'confirm_sale_lists';
    const UN_CONFIRM_SALE_LISTS = 'un_confirmed_sale_lists';
    const AUTO_PAY_SALE_LISTS = 'auto_pay_sale_lists';

    const VIEW_SALE_ITEMS = 'view_sale_items';
    const CREATE_SALE_ITEMS = 'create_sale_items';
    const UPDATE_SALE_ITEMS = 'update_sale_items';
    const DELETE_SALE_ITEMS = 'delete_sale_items';
    const MODIFY_SALE_ITEMS_PRICES = 'modify_sale_items_prices';

    const VIEW_SERVICE_ITEMS = 'view_service_items';
    const CREATE_SERVICE_ITEMS = 'create_service_items';
    const UPDATE_SERVICE_ITEMS = 'update_service_items';
    const DELETE_SERVICE_ITEMS = 'delete_service_items';

    const VIEW_EXPENSES = 'view_expenses';
    const CREATE_EXPENSES = 'create_expenses';
    const UPDATE_EXPENSES = 'update_expenses';
    const DELETE_EXPENSES = 'delete_expenses';

    const CREATE_NOTIFICATIONS = 'create_notifications';
    const DELETE_NOTIFICATIONS = 'delete_notifications';

    const VIEW_PAYMENTS = 'view_payments';

    const VIEW_PRODUCT_TRANSACTIONS = 'view_product_transactions';


    //Orders
    const FINISH_ORDERS = 'finish_orders';
    const VIEW_CUSTOMERS_ORDERS = 'view_customers_orders';
    const CANCEL_CUSTOMERS_ORDERS = 'cancel_customer_orders';

    //STATISTICS
    const VIEW_STATISTICS = 'view_statistics';
    const CONFIRM_CUSTOMER_ORDERS = 'confirm_customers_orders';
    const UPDATE_GLOBAL_OPTIONS = 'update_global_options';
    const VIEW_BILLS = 'view_bills';
    const PAY_BILLS = 'pay_bills';

    //Diagnoses
    const VIEW_DIAGNOSES = 'view_diagnoses';
    const CREATE_DIAGNOSES = 'create_diagnoses';
    const UPDATE_DIAGNOSES = 'update_diagnoses';
    const DELETE_DIAGNOSES = 'delete_diagnoses';


    const VIEW_PRODUCT_LOCATIONS = 'view_product_locations';
    const CREATE_PRODUCT_LOCATIONS = 'create_product_locations';
    const UPDATE_PRODUCT_LOCATIONS = 'update_product_locations';
    const DELETE_PRODUCT_LOCATIONS = 'delete_product_locations';

    const VIEW_PRODUCT_UNITS = 'view_product_units';
    const CREATE_PRODUCT_UNITS = 'create_product_units';
    const UPDATE_PRODUCT_UNITS = 'update_product_units';
    const DELETE_PRODUCT_UNITS = 'delete_product_units';

    const VIEW_STOCKHOLDERWITHDRAWALS = 'view_stockholderwithdrawals';
    const CREATE_STOCKHOLDERWITHDRAWALS = 'create_stockholderwithdrawals';
    const UPDATE_STOCKHOLDERWITHDRAWALS = 'update_stockholderwithdrawals';
    const DELETE_STOCKHOLDERWITHDRAWALS = 'delete_stockholderwithdrawals';

    public static function getAllValues(): array
    {
        $reflectionClass = new ReflectionClass(PermissionEnum::class);
        $constants = $reflectionClass->getConstants();
        return array_merge(array_values($constants), HRPermissionEnum::getAllValues());
    }

}
