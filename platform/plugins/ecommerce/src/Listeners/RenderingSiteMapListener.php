<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductTag;
use Botble\Theme\Events\RenderingSiteMapEvent;
use Botble\Theme\Facades\SiteMapManager;
use Illuminate\Support\Arr;

class RenderingSiteMapListener
{
    public function handle(RenderingSiteMapEvent $event): void
    {
        if ($key = $event->key) {
            switch ($key) {
                case 'product-tags':
                    $tags = ProductTag::query()
                        ->with('slugable')
                        ->wherePublished()
                        ->orderByDesc('created_at')
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($tags as $tag) {
                        if (! $tag->slugable) {
                            continue;
                        }

                        SiteMapManager::add($tag->url, $tag->updated_at, '0.3', 'weekly');
                    }

                    break;
                case 'product-categories':
                    $productCategories = ProductCategory::query()
                        ->with('slugable')
                        ->wherePublished()
                        ->orderByDesc('created_at')
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($productCategories as $productCategory) {
                        if (! $productCategory->slugable) {
                            continue;
                        }

                        SiteMapManager::add($productCategory->url, $productCategory->updated_at, '0.6');
                    }

                    break;
                case 'product-brands':
                    $brands = Brand::query()
                        ->with('slugable')
                        ->wherePublished()
                        ->orderByDesc('created_at')
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($brands as $brand) {
                        if (! $brand->slugable) {
                            continue;
                        }

                        SiteMapManager::add($brand->url, $brand->updated_at, '0.6');
                    }

                    break;
                case 'pages':
                    SiteMapManager::add(route('public.products'), null, '1', 'monthly');
                    if (EcommerceHelper::isCartEnabled()) {
                        SiteMapManager::add(route('public.cart'), null, '1', 'monthly');
                    }

                    break;
            }

            if (preg_match('/^products-((?:19|20|21|22)\d{2})-(0?[1-9]|1[012])$/', $key, $matches)) {
                if (($year = Arr::get($matches, 1)) && ($month = Arr::get($matches, 2))) {
                    $products = Product::query()
                        ->with('slugable')
                        ->wherePublished()
                        ->where('is_variation', 0)
                        ->whereYear('updated_at', $year)
                        ->whereMonth('updated_at', $month)
                        ->orderBy('updated_at', 'desc')
                        ->select(['id', 'name', 'updated_at'])
                        ->get();

                    foreach ($products as $product) {
                        if (! $product->slugable) {
                            continue;
                        }

                        SiteMapManager::add($product->url, $product->updated_at, '0.8');
                    }
                }
            }
        } else {
            $products = Product::query()
                ->selectRaw(
                    'YEAR(updated_at) as updated_year, MONTH(updated_at) as updated_month, MAX(updated_at) as updated_at'
                )
                ->where('is_variation', 0)
                ->groupBy('updated_year', 'updated_month')
                ->orderBy('updated_year', 'desc')
                ->orderBy('updated_month', 'desc')
                ->get();

            foreach ($products as $product) {
                $key = sprintf(
                    'products-%s-%s',
                    $product->updated_year,
                    str_pad($product->updated_month, 2, '0', STR_PAD_LEFT)
                );
                SiteMapManager::addSitemap(SiteMapManager::route($key), $product->updated_at);
            }

            $productCategory = ProductCategory::query()
                ->selectRaw('MAX(updated_at) as updated_at')
                ->wherePublished()
                ->first();
            if ($productCategory) {
                SiteMapManager::addSitemap(SiteMapManager::route('product-categories'), $productCategory->updated_at);
            }

            $brand = Brand::query()
                ->selectRaw('MAX(updated_at) as updated_at')
                ->wherePublished()
                ->first();
            if ($brand) {
                SiteMapManager::addSitemap(SiteMapManager::route('product-brands'), $brand->updated_at);
            }

            $productTag = ProductTag::query()
                ->selectRaw('MAX(updated_at) as updated_at')
                ->wherePublished()
                ->first();
            if ($productTag) {
                SiteMapManager::addSitemap(SiteMapManager::route('product-tags'), $productTag->updated_at);
            }
        }
    }
}
