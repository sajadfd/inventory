<?php

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\HR\Entities\Employer;

class EmployerSeeder extends Seeder
{

    public function run()
    {

        Employer::factory(10)->create();
    }
}
