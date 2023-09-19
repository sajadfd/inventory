<?php

namespace App\Services;

use Illuminate\Support\Str;

class StatisticsUtilitiesService
{

    public static function formatValue($parameterName, $parameterValue)
    {
        if (Str::endsWith($parameterName, '_price_usd')) {
            return number_format($parameterValue ?: 0, 2) . ' $';
        } else if (Str::endsWith($parameterName, '_price_iqd')) {
            return
                number_format($parameterValue) .
                " " .
                __('iqd');
        } else if (Str::endsWith($parameterName, '_percent')) {
            return round($parameterValue, 2, PHP_ROUND_HALF_UP) . ' %';
        } else {
            return (string)$parameterValue;
        }
    }

    public static function groupStatisticsArray($stats, $isFormatted = null)
    {
        if ($isFormatted === null) $isFormatted = request()->boolean('as-pdf') || request()->boolean('as-html') || request()->boolean('formatted');
        return array_reduce(array_keys($stats), function ($carry, $key) use ($stats, $isFormatted) {
            $value = $stats[$key];
            $value = $isFormatted ? static::formatValue($key, $value) : $value;
            $keyWithoutPrefix = Str::remove(['un_confirmed_', 'confirmed_'], $key);
            if (Str::startsWith($key, 'confirmed_')) {
                if (!isset($carry['confirmed'])) $carry['confirmed'] = [];
                $carry['confirmed'][$keyWithoutPrefix] = $value;
            } else if (Str::startsWith($key, 'un_confirmed_')) {
                if (!isset($carry['un_confirmed'])) $carry['un_confirmed'] = [];
                $carry['un_confirmed'][$keyWithoutPrefix] = $value;
            } else {
                if (!isset($carry['total'])) $carry['total'] = [];
                $carry['total'][$key] = $value;
            }
            return $carry;
        }, []);
    }

    public static function formatRecords($stats, $groupAble = true)
    {
        return array_reduce($stats, function ($carry, $groups) use ($groupAble) {

            $finalStats = [];

            foreach ($groups as $groupKey => &$groupData) {
                if ($groupAble) {
                    foreach ($groupData as $key => &$value) {
                        $value = static::formatValue($key, $value);
                    }

                    $finalStats[$groupKey] = $groupData;
                } else {
                    $value = static::formatValue($groupKey, $groupData);
                    $finalStats[$groupKey] = $value;
                }
            }
            $carry[] = $finalStats;
            return $carry;
        }, []);
    }
}
