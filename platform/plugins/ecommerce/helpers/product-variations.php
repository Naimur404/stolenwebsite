<?php

use Botble\Base\Facades\Html;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductAttributeSet;
use Botble\Ecommerce\Models\ProductVariation;
use Botble\Ecommerce\Models\ProductVariationItem;
use Botble\Ecommerce\Supports\RenderProductAttributeSetsOnSearchPageSupport;
use Botble\Ecommerce\Supports\RenderProductSwatchesSupport;
use Botble\Theme\Facades\Theme;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

if (! function_exists('render_product_swatches')) {
    function render_product_swatches(Product $product, array $params = []): string
    {
        $script = 'vendor/core/plugins/ecommerce/js/change-product-swatches.js';

        Theme::asset()->container('footer')->add('change-product-swatches', $script, ['jquery']);

        $selected = [];

        $params = array_merge([
            'selected' => $selected,
            'view' => 'plugins/ecommerce::themes.attributes.swatches-renderer',
        ], $params);

        $support = app(RenderProductSwatchesSupport::class);

        $html = $support->setProduct($product)->render($params);

        if (! request()->ajax()) {
            return $html;
        }

        return $html . Html::script($script)->toHtml();
    }
}

if (! function_exists('render_product_swatches_filter')) {
    function render_product_swatches_filter(array $params = []): string
    {
        return app(RenderProductAttributeSetsOnSearchPageSupport::class)->render($params);
    }
}

if (! function_exists('get_ecommerce_attribute_set')) {
    function get_ecommerce_attribute_set(): LengthAwarePaginator|Collection
    {
        return ProductAttributeSet::query()
            ->wherePublished()
            ->where('is_searchable', true)
            ->orderBy('order')
            ->with('attributes')
            ->get();
    }
}

if (! function_exists('get_parent_product')) {
    function get_parent_product(int|string $variationId, array $with = ['slugable']): Product
    {
        return ProductVariation::getParentOfVariation($variationId, $with);
    }
}

if (! function_exists('get_parent_product_id')) {
    function get_parent_product_id(int|string $variationId): ?int
    {
        $parent = get_parent_product($variationId);

        return $parent->getKey();
    }
}

if (! function_exists('get_product_info')) {
    function get_product_info(int|string $variationId): Collection
    {
        return ProductVariationItem::getVariationsInfo([$variationId]);
    }
}

if (! function_exists('get_product_attributes')) {
    function get_product_attributes(int|string $productId): Collection
    {
        return ProductVariationItem::getProductAttributes($productId);
    }
}
