<?php

namespace App\Http\Controllers;

use App\Enums\GlobalOptionEnum;
use App\Http\ApiResponse;
use App\Models\GlobalOption;
use App\Http\Requests\UpdateGlobalOptionRequest;
use App\Services\UploadImageService;
use Illuminate\Support\Facades\Cache;

class GlobalOptionController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(GlobalOption::class,'global_option');
    }

    public function index()
    {
        return ApiResponse::success(GlobalOption::query()->get()->reduce(function ($carry, $item) {
            $carry[$item->name] = $item->value;
            return $carry;
        }, []));
    }

    public function show(GlobalOption $globalOption)
    {
        return ApiResponse::success(GlobalOption::get($globalOption->name));
    }

    public function update(UpdateGlobalOptionRequest $request, GlobalOption $globalOption)
    {
        $data = $request->validated();
        if ($request->hasFile('value')) {
            (new UploadImageService($request->file('value')))
//                ->resizeTo(560)
                ->withThumbnail(false)->deleteAndSaveAuto($data, imageKey: 'value');
        }

        $globalOption->update($data);
        Cache::set('GP.' . $globalOption->name, $globalOption->value);
        return ApiResponse::success($globalOption->value);
    }
}
