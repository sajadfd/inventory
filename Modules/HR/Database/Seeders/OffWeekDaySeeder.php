<?php

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\HR\Entities\OffWeekDay;

class OffWeekDaySeeder extends Seeder
{

    public function run()
    {
        OffWeekDay::factory(20)->create();
    }
}
