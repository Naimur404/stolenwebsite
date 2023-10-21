<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Language\Models\LanguageMeta;
use Botble\Menu\Facades\Menu;
use Botble\Menu\Models\Menu as MenuModel;
use Botble\Menu\Models\MenuLocation;
use Botble\Menu\Models\MenuNode;
use Botble\Page\Models\Page;
use Illuminate\Support\Arr;

class MenuSeeder extends BaseSeeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Main menu',
                'slug' => 'main-menu',
                'location' => 'main-menu',
                'items' => [
                    [
                        'title' => 'Special Prices',
                        'url' => '/products/smart-watches',
                        'icon_font' => 'icon icon-tag',
                    ],
                    [
                        'title' => 'Pages',
                        'url' => '#',
                        'children' => [
                            [
                                'title' => 'About us',
                                'reference_id' => 2,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Terms Of Use',
                                'reference_id' => 3,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Terms & Conditions',
                                'reference_id' => 4,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Refund Policy',
                                'reference_id' => 5,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Coming soon',
                                'reference_id' => 12,
                                'reference_type' => Page::class,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Shop',
                        'url' => '/products',
                        'children' => [
                            [
                                'title' => 'All products',
                                'url' => '/products',
                            ],
                            [
                                'title' => 'Products Of Category',
                                'reference_id' => 15,
                                'reference_type' => ProductCategory::class,
                            ],
                            [
                                'title' => 'Product Single',
                                'url' => '/products/beat-headphone',
                            ],
                        ],
                    ],
                    [
                        'title' => 'Stores',
                        'url' => '/stores',
                    ],
                    [
                        'title' => 'Blog',
                        'reference_id' => 6,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'FAQs',
                        'reference_id' => 7,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Contact',
                        'reference_id' => 8,
                        'reference_type' => Page::class,
                    ],
                ],
            ],
            [
                'name' => 'Header menu',
                'slug' => 'header-menu',
                'location' => 'header-navigation',
                'items' => [
                    [
                        'title' => 'About Us',
                        'reference_id' => 2,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Wishlist',
                        'url' => 'wishlist',
                    ],
                    [
                        'title' => 'Order Tracking',
                        'url' => 'orders/tracking',
                    ],
                ],
            ],
            [
                'name' => 'Useful Links',
                'slug' => 'useful-links',
                'items' => [
                    [
                        'title' => 'Terms Of Use',
                        'reference_id' => 3,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Terms & Conditions',
                        'reference_id' => 4,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Refund Policy',
                        'reference_id' => 5,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'FAQs',
                        'reference_id' => 7,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => '404 Page',
                        'url' => '/nothing',
                    ],
                ],
            ],
            [
                'name' => 'Help Center',
                'slug' => 'help-center',
                'items' => [
                    [
                        'title' => 'About us',
                        'reference_id' => 2,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Affiliate',
                        'reference_id' => 10,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Career',
                        'reference_id' => 11,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Contact us',
                        'reference_id' => 8,
                        'reference_type' => Page::class,
                    ],
                ],
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'items' => [
                    [
                        'title' => 'Our blog',
                        'reference_id' => 6,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Cart',
                        'url' => '/cart',
                    ],
                    [
                        'title' => 'My account',
                        'url' => '/customer/overview',
                    ],
                    [
                        'title' => 'Shop',
                        'url' => '/products',
                    ],
                ],
            ],
        ];

        MenuModel::query()->truncate();
        MenuLocation::query()->truncate();
        MenuNode::query()->truncate();

        foreach ($data as $index => $item) {
            $menu = MenuModel::query()->create(Arr::except($item, ['items', 'location']));

            if (isset($item['location'])) {
                $menuLocation = MenuLocation::query()->create([
                    'menu_id' => $menu->id,
                    'location' => $item['location'],
                ]);

                LanguageMeta::saveMetaData($menuLocation);
            }

            foreach ($item['items'] as $menuNode) {
                $this->createMenuNode($index, $menuNode, $menu->id);
            }

            LanguageMeta::saveMetaData($menu);
        }

        Menu::clearCacheMenuItems();
    }

    protected function createMenuNode(
        int $index,
        array $menuNode,
        int|string $menuId,
        int|string|null $parentId = 0
    ): void {
        $menuNode['menu_id'] = $menuId;
        $menuNode['parent_id'] = $parentId;

        if (isset($menuNode['url'])) {
            $menuNode['url'] = str_replace(url(''), '', $menuNode['url']);
        }

        if (Arr::has($menuNode, 'children')) {
            $children = $menuNode['children'];
            $menuNode['has_child'] = true;

            unset($menuNode['children']);
        } else {
            $children = [];
            $menuNode['has_child'] = false;
        }

        $createdNode = MenuNode::query()->create($menuNode);

        if ($children) {
            foreach ($children as $child) {
                $this->createMenuNode($index, $child, $menuId, $createdNode->id);
            }
        }
    }
}
