<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface ProductListInterface
{
    public function person(): BelongsTo;

    public function items(): HasMany;
}
