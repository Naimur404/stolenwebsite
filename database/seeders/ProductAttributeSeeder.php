<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\ProductAttribute;
use Botble\Ecommerce\Models\ProductAttributeSet;

class ProductAttributeSeeder extends BaseSeeder
{
    public function run(): void
    {
        ProductAttributeSet::query()->truncate();

        ProductAttributeSet::query()->create([
            'title' => 'Color',
            'slug' => 'color',
            'display_layout' => 'visual',
            'is_searchable' => true,
            'is_use_in_product_listing' => true,
            'order' => 0,
        ]);

        ProductAttributeSet::query()->create([
            'title' => 'Size',
            'slug' => 'size',
            'display_layout' => 'text',
            'is_searchable' => true,
            'is_use_in_product_listing' => true,
            'order' => 1,
        ]);

        ProductAttributeSet::query()->create([
            'title' => 'Weight',
            'slug' => 'weight',
            'display_layout' => 'text',
            'is_searchable' => true,
            'is_use_in_product_listing' => true,
            'order' => 0,
        ]);

        ProductAttributeSet::query()->create([
            'title' => 'Boxes',
            'slug' => 'boxes',
            'display_layout' => 'text',
            'is_searchable' => true,
            'is_use_in_product_listing' => true,
            'order' => 1,
        ]);

        ProductAttribute::query()->truncate();

        $productAttributes = [
            [
                'attribute_set_id' => 1,
                'title' => 'Green',
                'slug' => 'green',
                'color' => '#5FB7D4',
                'is_default' => true,
                'order' => 1,
            ],
            [
                'attribute_set_id' => 1,
                'title' => 'Blue',
                'slug' => 'blue',
                'color' => '#333333',
                'is_default' => false,
                'order' => 2,
            ],
            [
                'attribute_set_id' => 1,
                'title' => 'Red',
                'slug' => 'red',
                'color' => '#DA323F',
                'is_default' => false,
                'order' => 3,
            ],
            [
                'attribute_set_id' => 1,
                'title' => 'Black',
                'slug' => 'black',
                'color' => '#2F366C',
                'is_default' => false,
                'order' => 4,
            ],
            [
                'attribute_set_id' => 1,
                'title' => 'Brown',
                'slug' => 'brown',
                'color' => '#87554B',
                'is_default' => false,
                'order' => 5,
            ],
            [
                'attribute_set_id' => 2,
                'title' => 'S',
                'slug' => 's',
                'is_default' => true,
                'order' => 1,
            ],
            [
                'attribute_set_id' => 2,
                'title' => 'M',
                'slug' => 'm',
                'is_default' => false,
                'order' => 2,
            ],
            [
                'attribute_set_id' => 2,
                'title' => 'L',
                'slug' => 'l',
                'is_default' => false,
                'order' => 3,
            ],
            [
                'attribute_set_id' => 2,
                'title' => 'XL',
                'slug' => 'xl',
                'is_default' => false,
                'order' => 4,
            ],
            [
                'attribute_set_id' => 2,
                'title' => 'XXL',
                'slug' => 'xxl',
                'is_default' => false,
                'order' => 5,
            ],

            [
                'attribute_set_id' => 3,
                'title' => '1KG',
                'slug' => '1kg',
                'is_default' => true,
                'order' => 1,
            ],
            [
                'attribute_set_id' => 3,
                'title' => '2KG',
                'slug' => '2kg',
                'is_default' => false,
                'order' => 2,
            ],
            [
                'attribute_set_id' => 3,
                'title' => '3KG',
                'slug' => '3kg',
                'is_default' => false,
                'order' => 3,
            ],
            [
                'attribute_set_id' => 3,
                'title' => '4KG',
                'slug' => '4kg',
                'is_default' => false,
                'order' => 4,
            ],
            [
                'attribute_set_id' => 3,
                'title' => '5KG',
                'slug' => '5kg',
                'is_default' => false,
                'order' => 5,
            ],
            [
                'attribute_set_id' => 4,
                'title' => '1 Box',
                'slug' => '1box',
                'is_default' => true,
                'order' => 1,
            ],
            [
                'attribute_set_id' => 4,
                'title' => '2 Boxes',
                'slug' => '2boxes',
                'is_default' => false,
                'order' => 2,
            ],
            [
                'attribute_set_id' => 4,
                'title' => '3 Boxes',
                'slug' => '3boxes',
                'is_default' => false,
                'order' => 3,
            ],
            [
                'attribute_set_id' => 4,
                'title' => '4 Boxes',
                'slug' => '4boxes',
                'is_default' => false,
                'order' => 4,
            ],
            [
                'attribute_set_id' => 4,
                'title' => '5 Boxes',
                'slug' => '5boxes',
                'is_default' => false,
                'order' => 5,
            ],
        ];

        foreach ($productAttributes as $item) {
            ProductAttribute::query()->create($item);
        }
    }
}
