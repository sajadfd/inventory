<?php

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\HR\Entities\Attendance;

class AttendanceSeeder extends Seeder
{

    public function run()
    {
        Attendance::factory(20)->create();
    }
}
