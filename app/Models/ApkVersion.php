<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperApkVersion
 */
class ApkVersion extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'uuid';
    public $incrementing = false;

    protected $hidden=[
      'file_path',
    ];

    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        self::creating(function (ApkVersion $apkVersion) {
            $apkVersion->uuid = \Str::uuid();
        });
    }
}
