<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Brand;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Str;

class BrandSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('brands');

        $brands = [
            [
                'logo' => 'brands/1.png',
                'name' => 'FoodPound',
                'description' => 'New Snacks Release',
            ],
            [
                'logo' => 'brands/2.png',
                'name' => 'iTea JSC',
                'description' => 'Happy Tea 100% Organic. From $29.9',
            ],
            [
                'logo' => 'brands/3.png',
                'name' => 'Soda Brand',
                'description' => 'Fresh Meat Sausage. BUY 2 GET 1!',
            ],
            [
                'logo' => 'brands/4.png',
                'name' => 'Farmart',
                'description' => 'Fresh Meat Sausage. BUY 2 GET 1!',
            ],
            [
                'logo' => 'brands/3.png',
                'name' => 'Soda Brand',
                'description' => 'Fresh Meat Sausage. BUY 2 GET 1!',
            ],
        ];

        Brand::query()->truncate();

        foreach ($brands as $key => $item) {
            $item['order'] = $key;
            $item['is_featured'] = true;
            $brand = Brand::query()->create($item);

            Slug::query()->create([
                'reference_type' => Brand::class,
                'reference_id' => $brand->id,
                'key' => Str::slug($brand->name),
                'prefix' => SlugHelper::getPrefix(Brand::class),
            ]);
        }
    }
}
