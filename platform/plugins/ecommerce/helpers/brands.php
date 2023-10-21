<?php

use Botble\Ecommerce\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

if (! function_exists('get_featured_brands')) {
    function get_featured_brands(int $limit = 8, array $with = ['slugable'], array $withCount = []): Collection|LengthAwarePaginator
    {
        return Brand::query()
            ->where('is_featured', true)
            ->wherePublished()
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->with($with)
            ->withCount($withCount)
            ->take($limit)
            ->get();
    }
}

if (! function_exists('get_all_brands')) {
    function get_all_brands(array $conditions = [], array $with = ['slugable'], array $withCount = []): Collection
    {
        return Brand::query()
            ->where($conditions)
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->with($with)
            ->withCount($withCount)
            ->get();
    }
}
