<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\ProductCollection;
use Illuminate\Support\Str;

class ProductCollectionSeeder extends BaseSeeder
{
    public function run(): void
    {
        ProductCollection::query()->truncate();

        $productCollections = [
            [
                'name' => 'New Arrival',
            ],
            [
                'name' => 'Best Sellers',
            ],
            [
                'name' => 'Special Offer',
            ],
        ];

        ProductCollection::query()->truncate();

        foreach ($productCollections as $item) {
            $item['slug'] = Str::slug($item['name']);

            ProductCollection::query()->create($item);
        }
    }
}
