<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\HR\Http\Controllers\SalaryController;

class SalariesCalculateCommand extends Command
{

    protected $signature = 'salaries-calculate';


    protected $description = 'Command description.';

    public function handle()
    {

        $controller = new SalaryController();
        $response = $controller->calculate();
        $this->info($response->content());
    }

}
