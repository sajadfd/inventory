<?php

namespace App\Models;

use App\Enums\GlobalOptionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperGlobalOption
 */
class GlobalOption extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $primaryKey = 'name';
    public $incrementing = false;

    protected $guarded = [];

    public static function get(string $optionName, $raw = false, $defaultValue = null)
    {
        if ($raw) {
            return static::GetAndSetCacheRaw($optionName, $defaultValue);
        }
        return static::GetAndSetCache($optionName, $defaultValue);
    }

    public static function getRaw(string $optionName, $defaultValue = null)
    {
        return static::GetAndSetCacheRaw($optionName, $defaultValue);
    }

    protected static function GetAndSetCache(string $optionName, $defaultValue = null)
    {
        $value = Cache::get('GP.' . $optionName);
        if ($value === null) {
            $value = GlobalOption::query()->find($optionName)?->value ?: $defaultValue;
            Cache::set('GP.' . $optionName, $value);
        }
        return $value;
    }

    protected static function GetAndSetCacheRaw(string $optionName, $defaultValue = null)
    {
        $value = Cache::get('GPRaw.' . $optionName);
        if (!$value) {
            $value = GlobalOption::query()->find($optionName)?->getRawOriginal('value') ?: $defaultValue;
            Cache::set('GPRaw.' . $optionName, $value);
        }
        return $value;
    }

    protected $hidden = ['id', 'updated_at', 'created_at'];

    public function getValueAttribute()
    {
        return self::castTo($this->name, $this->original['value']);
    }

    public static function castTo($name, $value)
    {
        return match ($name) {
            GlobalOptionEnum::IqdSaleToNearestPayablePrice => (bool)$value,
            GlobalOptionEnum::CurrencyValue => (float)$value,
            GlobalOptionEnum::PaginatorPerPage => (int)$value,
            GlobalOptionEnum::HeaderImage, GlobalOptionEnum::FooterImage => $value ? config('app.url') . $value : '',
            default => $value,
        };
    }

    public static function GetCurrencyValue()
    {
        return static::GetAndSetCache(GlobalOptionEnum::CurrencyValue, 1);
    }

    public static function GetIqdSaleToNearestPayablePrice(): bool
    {
        return static::GetAndSetCache(GlobalOptionEnum::IqdSaleToNearestPayablePrice, true);
    }

    public static function GetInventoryNameValue()
    {
        return static::GetAndSetCache(GlobalOptionEnum::InventoryName, 'Inventory Name');
    }

    public static function GetPaginatorLimitValue()
    {
        return static::GetAndSetCache(GlobalOptionEnum::PaginatorPerPage, -1);
    }

}
