<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperCar
 */
class Car extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable,CreatedByTrait;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function carType()
    {
        return $this->belongsTo(CarType::class);
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function saleLists()
    {
        return $this->hasMany(SaleList::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    //Scopes
    public function scopeBasicRelations(Builder $query): void
    {
        $query->with('customer')->with('carType')->with('carModel')->with('color');
    }

    //Custom Attributes
    public function getCustomerNameAttribute()
    {
        return $this->customer?->name;
    }

    public function getCarTypeNameAttribute()
    {
        return $this->carType?->name;
    }

    public function getCarModelNameAttribute()
    {
        return $this->carModel?->name;
    }

    public function getColorNameAttribute()
    {
        return $this->color?->name;
    }

    public function getColorCodeAttribute()
    {
        return $this->color?->code;
    }


}
