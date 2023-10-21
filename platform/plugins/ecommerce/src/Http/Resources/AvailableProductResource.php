<?php

namespace Botble\Ecommerce\Http\Resources;

use Botble\Ecommerce\Models\Product;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin Product
 */
class AvailableProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $name = $this->name;
        if (is_plugin_active('marketplace') && $this->original_product->store_id && $this->original_product->store->name) {
            $name .= ' (' . $this->original_product->store->name . ')';
        }
        $taxPrice = $this->front_sale_price * $this->total_taxes_percentage / 100;

        return [
            'id' => $this->id,
            'name' => $name,
            'sku' => $this->sku,
            'description' => $this->description,
            'slug' => $this->slug,
            'with_storehouse_management' => $this->with_storehouse_management,
            'quantity' => $this->quantity,
            'is_out_of_stock' => $this->isOutOfStock(),
            'stock_status_label' => $this->stock_status_label,
            'stock_status_html' => $this->stock_status_html,
            'price' => $this->front_sale_price,
            'formatted_price' => format_price($this->front_sale_price),
            'final_price' => $this->front_sale_price + $taxPrice,
            'original_price' => $this->original_price,
            'tax_price' => $taxPrice,
            'total_taxes_percentage' => $this->total_taxes_percentage,
            'image_with_sizes' => $this->image_with_sizes,
            'weight' => $this->weight,
            'height' => $this->height,
            'wide' => $this->wide,
            'length' => $this->length,
            'product_link' => Auth::user()->hasPermission('products.edit') ? route('products.edit', $this->original_product->id) : '',
            'image_url' => RvMedia::getImageUrl($this->image, 'thumb', false, RvMedia::getDefaultImage()),
            'is_variation' => $this->is_variation,
            'variations' => $this->variations->map(function ($item) {
                return (new self($item->product));
            }),
            'product_options' => ProductOptionResource::collection($this->is_variation ? $this->original_product->options : $this->options),
            $this->mergeWhen($this->is_variation, function () {
                return [
                    'variation_attributes' => $this->variation_attributes,
                ];
            }),
            $this->mergeWhen(is_plugin_active('marketplace'), function () {
                return [
                    'store_id' => $this->original_product->store_id,
                    'store' => [
                        'id' => $this->original_product->store->id,
                        'name' => $this->original_product->store->name,
                    ],
                ];
            }),
            'original_product_id' => $this->original_product->id,
        ];
    }
}
