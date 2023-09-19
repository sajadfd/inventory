<?php

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\HR\Entities\Absence;

class AbsenceSeeder extends Seeder
{

    public function run()
    {
        Absence::factory(20)->create();
    }
}
