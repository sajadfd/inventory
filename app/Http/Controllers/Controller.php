<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;
use OpenApi\Attributes\Info;
use OpenApi\Attributes\SecurityScheme;

#[Info(version: '1.0',title: "Inventory Api Documentation Demo")]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
