<?php

namespace Database\Seeders;

use Botble\Ecommerce\Models\ProductLabel;
use Illuminate\Database\Seeder;

class ProductLabelSeeder extends Seeder
{
    public function run(): void
    {
        ProductLabel::query()->truncate();

        $productCollections = [
            [
                'name' => 'Hot',
                'color' => '#cb4321',
            ],
            [
                'name' => 'New',
                'color' => '#00c9a7',
            ],
            [
                'name' => 'Sale',
                'color' => '#ba591d',
            ],
        ];

        foreach ($productCollections as $item) {
            ProductLabel::query()->create($item);
        }
    }
}
