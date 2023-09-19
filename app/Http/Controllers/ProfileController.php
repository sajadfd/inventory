<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\ApiResponse;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\Profile;
use App\Services\UploadImageService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        if ($profile = $user->profile) {
            $data = $request->validated();

            (new UploadImageService)->deleteAndSaveAuto($data);

            $profile->update($data);

            if ($user->type === UserType::Customer) {
                $user->customer?->update([
                    'name' => $profile->full_name,
                    'address' => $profile->address,
                    'phone' => $user->phone,
                    'image' => $profile->image,
                    'thumbnail' => $profile->thumbnail,
                ]);
            } else if ($user->type === UserType::Driver) {
                $user->driver?->update([
                    'name' => $profile->full_name,
                    'address' => $profile->address,
                    'phone' => $user->phone,
                    'image' => $profile->image,
                    'thumbnail' => $profile->thumbnail,
                ]);
            }

            $user->load('profile');
            return ApiResponse::success(UserResource::make($user));
        } else {
            return ApiResponse::error('', 404);
        }
    }

}
