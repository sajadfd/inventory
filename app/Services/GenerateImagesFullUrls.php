<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GenerateImagesFullUrls
{
    public static function Generate(Model &$model, $imageProperty = 'image', $thumbnailProperty = 'thumbnail'): Model
    {
        if (!empty($model->{$imageProperty}) && file_exists($fullPath = public_path($model->{$imageProperty})) && !is_dir($fullPath)) {
            $model->{$imageProperty} = url($model->{$imageProperty});
        } else {
            $model->{$imageProperty} = null;
        }
        if (!empty($model->{$thumbnailProperty}) && file_exists($fullPath = public_path($model->{$thumbnailProperty})) && !is_dir($fullPath)) {
            $model->{$thumbnailProperty} = url($model->{$thumbnailProperty});
        } else {
            $model->{$thumbnailProperty} = null;
        }
        return $model;
    }
}
