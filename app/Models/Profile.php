<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperProfile
 */
class Profile extends Model implements Auditable
{
    use CreatedByTrait,\OwenIt\Auditing\Auditable;

    protected $guarded=[];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

}
