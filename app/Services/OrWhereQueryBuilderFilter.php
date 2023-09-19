<?php

namespace App\Services;

use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class OrWhereQueryBuilderFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        $query->orWhereRaw("LOWER({$property}) LIKE ?", ["%{$value}%"]);
    }
}
