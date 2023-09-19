<?php

namespace App\Services;

use App\Enums\GlobalOptionEnum;
use App\Models\GlobalOption;
use Illuminate\Console\Command;

class GlobalOptionsSync
{
    public function defaultOptions()
    {
        return [
            GlobalOptionEnum::CurrencyValue => 1450,
            GlobalOptionEnum::HeaderImage => '',
            GlobalOptionEnum::FooterImage => '',
            GlobalOptionEnum::InventoryName => 'Inventory',
            GlobalOptionEnum::PaginatorPerPage => -1,
            GlobalOptionEnum::IqdSaleToNearestPayablePrice => true,
            GlobalOptionEnum::WhatsappContact => '',
            GlobalOptionEnum::FacebookContact => '',
            GlobalOptionEnum::InstagramContact => '',
            GlobalOptionEnum::YoutubeContact => '',
            GlobalOptionEnum::TwitterContact => '',
            GlobalOptionEnum::PhoneContact => '',
            GlobalOptionEnum::AddressContact => '',
            GlobalOptionEnum::UnifyUnpaidSalaries => false,
            GlobalOptionEnum::ConsiderAllMonthsAsThirtyDays => false,
        ];
    }

    public function run(?Command $command = null)
    {
        GlobalOption::disableAuditing();
        $currentGlobalOptionNames = GlobalOption::query()->pluck('name')->toArray();
        foreach ($this->defaultOptions() as $optionName => $optionValue) {
            if (!in_array($optionName, $currentGlobalOptionNames)) {
                GlobalOption::query()->create(['name' => $optionName, 'value' => $optionValue]);
                $command->info("Option `$optionName` Created with Value `$optionValue`.");
            }
        }
    }
}
