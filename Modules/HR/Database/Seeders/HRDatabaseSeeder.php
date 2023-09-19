<?php

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class HRDatabaseSeeder extends Seeder
{

    public function run()
    {

        $this->call([
            EmployerSeeder::class,
            ContractSeeder::class,
            AbsenceSeeder::class,
            AttendanceSeeder::class,
            OffDateSeeder::class,
            OffWeekDaySeeder::class,
            LoanSeeder::class,
        ]);
    }
}
