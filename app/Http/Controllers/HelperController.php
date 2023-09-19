<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HelperController extends Controller
{
    public function migrateFresh()
    {
        if (request()->boolean('seed')) {
            $res = Artisan::call('migrate:fresh --seed');
        } else {
            $res = Artisan::call('migrate:fresh');
        }
        return $res;
    }

    public function seed()
    {
        return Artisan::call('db:seed');
    }
}
