<?php

namespace Database\Seeders;

use Botble\Base\Facades\MetaBox;
use Botble\Base\Models\MetaBox as MetaBoxModel;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductCategorySeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('product-categories');

        $categories = [
            [
                'name' => 'Fruits & Vegetables',
                'is_featured' => true,
                'image' => 'product-categories/1.png',
                'icon' => 'icon-star',
                'children' => [
                    [
                        'name' => 'Fruits',
                        'children' => [
                            ['name' => 'Apples'],
                            ['name' => 'Bananas'],
                            ['name' => 'Berries'],
                            ['name' => 'Oranges & Easy Peelers'],
                            ['name' => 'Grapes'],
                            ['name' => 'Lemons & Limes'],
                            ['name' => 'Peaches & Nectarines'],
                            ['name' => 'Pears'],
                            ['name' => 'Melon'],
                            ['name' => 'Avocados'],
                            ['name' => 'Plums & Apricots'],
                        ],
                    ],
                    [
                        'name' => 'Vegetables',
                        'children' => [
                            ['name' => 'Potatoes'],
                            ['name' => 'Carrots & Root Vegetables'],
                            ['name' => 'Broccoli & Cauliflower'],
                            ['name' => 'Cabbage, Spinach & Greens'],
                            ['name' => 'Onions, Leeks & Garlic'],
                            ['name' => 'Mushrooms'],
                            ['name' => 'Tomatoes'],
                            ['name' => 'Beans, Peas & Sweetcorn'],
                            ['name' => 'Freshly Drink Orange Juice'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Breads Sweets',
                'is_featured' => true,
                'image' => 'product-categories/2.png',
                'icon' => 'icon-bread',
                'children' => [
                    [
                        'name' => 'Crisps, Snacks & Nuts',
                        'children' => [
                            ['name' => 'Crisps & Popcorn'],
                            ['name' => 'Nuts & Seeds'],
                            ['name' => 'Lighter Options'],
                            ['name' => 'Cereal Bars'],
                            ['name' => 'Breadsticks & Pretzels'],
                            ['name' => 'Fruit Snacking'],
                            ['name' => 'Rice & Corn Cakes'],
                            ['name' => 'Protein & Energy Snacks'],
                            ['name' => 'Toddler Snacks'],
                            ['name' => 'Meat Snacks'],
                            ['name' => 'Beans'],
                            ['name' => 'Lentils'],
                            ['name' => 'Chickpeas'],
                        ],
                    ],
                    [
                        'name' => 'Tins & Cans',
                        'children' => [
                            ['name' => 'Tomatoes'],
                            ['name' => 'Baked Beans, Spaghetti'],
                            ['name' => 'Fish'],
                            ['name' => 'Beans & Pulses'],
                            ['name' => 'Fruit'],
                            ['name' => 'Coconut Milk & Cream'],
                            ['name' => 'Lighter Options'],
                            ['name' => 'Olives'],
                            ['name' => 'Sweetcorn'],
                            ['name' => 'Carrots'],
                            ['name' => 'Peas'],
                            ['name' => 'Mixed Vegetables'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Frozen Seafoods',
                'is_featured' => true,
                'image' => 'product-categories/3.png',
                'icon' => 'icon-hamburger',
            ],
            [
                'name' => 'Raw Meats',
                'is_featured' => true,
                'image' => 'product-categories/4.png',
                'icon' => 'icon-steak',
            ],
            [
                'name' => 'Wines & Alcohol Drinks',
                'is_featured' => true,
                'image' => 'product-categories/5.png',
                'icon' => 'icon-glass',
                'children' => [
                    [
                        'name' => 'Ready Meals',
                        'children' => [
                            ['name' => 'Meals for 1'],
                            ['name' => 'Meals for 2'],
                            ['name' => 'Indian'],
                            ['name' => 'Italian'],
                            ['name' => 'Chinese'],
                            ['name' => 'Traditional British'],
                            ['name' => 'Thai & Oriental'],
                            ['name' => 'Mediterranean & Moroccan'],
                            ['name' => 'Mexican & Caribbean'],
                            ['name' => 'Lighter Meals'],
                            ['name' => 'Lunch & Veg Pots'],
                        ],
                    ],
                    [
                        'name' => 'Salad & Herbs',
                        'children' => [
                            ['name' => 'Salad Bags'],
                            ['name' => 'Cucumber'],
                            ['name' => 'Tomatoes'],
                            ['name' => 'Lettuce'],
                            ['name' => 'Lunch Salad Bowls'],
                            ['name' => 'Lunch Salad Bowls'],
                            ['name' => 'Fresh Herbs'],
                            ['name' => 'Avocados'],
                            ['name' => 'Peppers'],
                            ['name' => 'Coleslaw & Potato Salad'],
                            ['name' => 'Spring Onions'],
                            ['name' => 'Chilli, Ginger & Garlic'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Tea & Coffee',
                'is_featured' => true,
                'image' => 'product-categories/6.png',
                'icon' => 'icon-teacup',
            ],
            [
                'name' => 'Milks and Dairies',
                'is_featured' => true,
                'image' => 'product-categories/7.png',
                'icon' => 'icon-coffee-cup',
            ],
            [
                'name' => 'Pet Foods',
                'is_featured' => true,
                'image' => 'product-categories/8.png',
                'icon' => 'icon-hotdog',
            ],
            [
                'name' => 'Food Cupboard',
                'is_featured' => true,
                'image' => 'product-categories/1.png',
                'icon' => 'icon-cheese',

            ],
        ];

        ProductCategory::query()->truncate();
        Slug::query()->where('reference_type', ProductCategory::class)->delete();
        MetaBoxModel::query()->where('reference_type', ProductCategory::class)->delete();

        foreach ($categories as $index => $item) {
            $this->createCategoryItem($index, $item);
        }
    }

    protected function createCategoryItem(int $index, array $category, int|string|null $parentId = 0): void
    {
        $category['parent_id'] = $parentId;
        $category['order'] = $index;

        if (Arr::has($category, 'children')) {
            $children = $category['children'];
            unset($category['children']);
        } else {
            $children = [];
        }

        $createdCategory = ProductCategory::query()->create(Arr::except($category, ['icon']));

        Slug::query()->create([
            'reference_type' => ProductCategory::class,
            'reference_id' => $createdCategory->id,
            'key' => Str::slug($createdCategory->name),
            'prefix' => SlugHelper::getPrefix(ProductCategory::class),
        ]);

        if (isset($category['icon'])) {
            MetaBox::saveMetaBoxData($createdCategory, 'icon', $category['icon']);
        }

        if ($children) {
            foreach ($children as $childIndex => $child) {
                $this->createCategoryItem($childIndex, $child, $createdCategory->id);
            }
        }
    }
}
