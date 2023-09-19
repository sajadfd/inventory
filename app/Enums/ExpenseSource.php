<?php

namespace App\Enums;

enum ExpenseSource: string
{
    case InventoryExpense = 'inventory_expense';
    case StoreExpense = 'store_expense';

    public static function getAllValues(): array
    {
        return array_column(ExpenseSource::cases(), 'value');
    }
}
