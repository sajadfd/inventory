<?php

namespace App\Traits;

trait BillableAttributesTrait
{
    public function getDebtsAttribute()
    {
        return $this->bills->sum('remaining_price');
    }

    public function getBillsTotalPriceAttribute()
    {
        return $this->bills->sum('total_price');
    }

    public function getBillsTotalCountAttribute()
    {
        return $this->bills->count();
    }

    public function getBillsUnPayedCountAttribute()
    {
        return $this->bills->where('is_payed', false)->count();
    }
}
