<?php

namespace Modules\HR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\Employer;

class ContractSeeder extends Seeder
{

    public function run()
    {
        $allowOnlyOneContractEachEmployer = true;
        if ($allowOnlyOneContractEachEmployer) {
            $cnt = Employer::query()->whereDoesntHave('contracts')->count();
            if ($cnt) {
                Contract::factory($cnt)->create();
            } else {
                Contract::factory(10)->create();
            }
        } else {
            Contract::factory(20)->create();
        }
    }
}
