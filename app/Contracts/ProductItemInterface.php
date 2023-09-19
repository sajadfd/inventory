<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property int $id
 */
interface ProductItemInterface
{

    public function transactions(): MorphMany;

    public function list(): BelongsTo;

}
