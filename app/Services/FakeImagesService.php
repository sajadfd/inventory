<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class FakeImagesService
{
    public static function make($type = 'product')
    {
        $url = config('app.env') === 'testing' ? "/files/{$type}_thumbnail.png"
            : (config('app.local_faker_images') && file_exists($path = resource_path("fakes/{$type}_thumbnail.png")) ?
                '/files/thumbnails/' . (File::copy($path, public_path('/files/thumbnails/' . ($target = rand(1, 1000) . time() . File::hash($path) . '.png'))) ? $target : '')
                : '/files/thumbnails/' . fake()->image(public_path('files/thumbnails'), 100, 100, category: $type, fullPath: false));
        return $url;
    }
}
