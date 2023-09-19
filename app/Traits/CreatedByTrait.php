<?php

namespace App\Traits;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

trait CreatedByTrait
{

    public static function bootCreatedByTrait(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                if (!(property_exists($model, 'disableCreatedBy') && $model->disableCreatedBy === true)) {
                    $model->created_by = auth()->id();
                }
            }
        });


//        static::updating(function ($model) {
//            if (auth()->check()) {
//                if (!(property_exists($model, 'disableUpdatedBy') && $model->disableUpdatedBy)) {
//                    $model->updated_by = auth()->id();
//                }
//            }
//        });

//        updating updated_by when model is updated
//        static::deleting(function ($model) {
//            if (auth()->check()) {
//                $model->deleted_by = auth()->id();
//            }
//        });

    }
}
