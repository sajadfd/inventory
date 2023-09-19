<?php

namespace App\Services;

use App\Models\GlobalOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use Str;

class PaginatorService
{
    public QueryBuilder $queryBuilder;

    public int $totalWithoutFilters;
    public ?LengthAwarePaginator $paginatorData = null;

    public function __construct(public Builder|HasMany|HasManyThrough $query, public ?string $jsonResource = null, public ?\Closure $transformer = null, public ?int $perPage = null, public bool|\Closure $useQueryBuilder = false)
    {
        if ($this->useQueryBuilder) {
            $this->queryBuilder = QueryBuilder::for($query);
        }
        return $this;
    }

    public static function make(Builder|HasMany|HasManyThrough $query, ?string $jsonResource = null, ?\Closure $transformer = null, ?int $perPage = null, bool|\Closure $useQueryBuilder = false): PaginatorService
    {
        return (new PaginatorService($query, $jsonResource, $transformer, $perPage, $useQueryBuilder))->generate();
    }

    public static function from(Builder|HasMany|HasManyThrough $query, ?string $jsonResource = null, ?\Closure $transformer = null, ?int $perPage = null, bool|\Closure $useQueryBuilder = false): array
    {
        return (new PaginatorService($query, $jsonResource, $transformer, $perPage, $useQueryBuilder))->generate()->proceed();
    }

    public function getPerPage()
    {
        $perPage = ((int)request('per_page')) ?: GlobalOption::GetPaginatorLimitValue();
        if ($perPage < 0) {
            $perPage = $this->query->count();
        }
        return $perPage;
    }

    public function generate(): self
    {
        if ($this->useQueryBuilder) {
            $totalWithoutFilters = $this->query->count();
            $queryBuilder = $this->queryBuilder;
            if (is_callable($this->useQueryBuilder)) {
                call_user_func_array($this->useQueryBuilder, array(&$queryBuilder));
            } else {
                $queryBuilder->allowedFilters('name');
            }
            $paginatorData = $queryBuilder->paginate($this->getPerPage());
            $paginatorData->appends(request()->query());
        } else {
            $paginatorData = $this->query->paginate($this->getPerPage());
            $totalWithoutFilters = $paginatorData->total();
        }

        if ($this->jsonResource) {
            $paginatorData->getCollection()->transform(function ($value) {
                $json = call_user_func_array([$this->jsonResource, 'make'], [$value]);
                if ($excludes = request('exclude')) {
                    foreach (explode(',', $excludes) as $excludedKey) {
                        data_forget($json, $excludedKey);

                    }
                }
                return $json;
            });

        } else if ($this->transformer) {
            $paginatorData->getCollection()->transform($this->transformer);
        }

        $this->paginatorData = $paginatorData;
        $this->totalWithoutFilters = $totalWithoutFilters;

        return $this;
    }

    public function proceed(): array
    {
        if ($this->paginatorData === null) {
            $this->generate();
        }
        $paginatorData = $this->paginatorData;
        return [
            'current_page' => $paginatorData->currentPage(),
            'from' => $paginatorData->firstItem(),
            'last_page' => $paginatorData->lastPage(),
            'per_page' => $paginatorData->perPage(),
            'to' => $paginatorData->lastItem(),
            'total' => $paginatorData->total(),
            'total_without_filters' => $this->totalWithoutFilters,
            'data' => $paginatorData->items(),
        ];
    }

}
