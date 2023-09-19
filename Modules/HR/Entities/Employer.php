<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;


/**
 * @mixin IdeHelperEmployer
 */
class Employer extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\EmployerFactory::new();
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(Contract::class)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')->orderBy('id', 'desc')
            ->with(Contract::$defaultRelations);
    }
}
