<?php

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\HR\Database\factories\SalaryFactory;
use Modules\HR\Entities\Salary;

class SalarySeeder extends Seeder
{
    public function run()
    {
        Salary::factory(10)->create();
    }
}
