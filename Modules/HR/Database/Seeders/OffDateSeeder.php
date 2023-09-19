<?php

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\HR\Entities\OffDate;

class OffDateSeeder extends Seeder
{

    public function run()
    {

        OffDate::factory(20)->create();
    }
}
