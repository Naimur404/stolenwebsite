<?php

namespace Botble\Ecommerce\Http\Resources;

use Botble\Ecommerce\Cart\CartItem;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin CartItem
 */
class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $originalPrice = Arr::get($this->options, 'original_price');
        $taxPrice = $this->price * Arr::get($this->options, 'taxRate') / 100;
        $totalPrice = $this->price * $this->qty;

        $options = collect(Arr::get($this->options, 'options.optionCartValue', []))->map(function ($item) use ($originalPrice) {
            $affectType = Arr::get(Arr::first($item), 'affect_type');
            $affectPrice = Arr::get(Arr::first($item), 'affect_price');

            return [
                'option_type' => Arr::get(Arr::first($item), 'option_type'),
                'values' => Arr::get(Arr::first($item), 'option_value'),
                'affect_type' => $affectType,
                'affect_price' => $affectPrice,
                'price_label' => $affectType == 1 ? format_price($originalPrice * $affectPrice / 100) : format_price($affectPrice),
            ];
        });

        $optionValues = collect(Arr::get($this->options, 'options.optionCartValue', []))
            ->map(function ($items, $key) use ($originalPrice) {
                $values = [];
                foreach ($items as $k => $item) {
                    $affectType = Arr::get($item, 'affect_type');
                    $affectPrice = Arr::get($item, 'affect_price');
                    $values[] =[
                        'id' => $k,
                        'option_type' => Arr::get($item, 'option_type'),
                        'value' => Arr::get($item, 'option_value'),
                        'affect_type' => $affectType,
                        'affect_price' => $affectPrice,
                        'price_label' => $affectType == 1 ? format_price($originalPrice * $affectPrice / 100) : format_price($affectPrice),
                    ];
                }

                return [
                    'id' => $key,
                    'title' => Arr::get($this->options, 'options.optionInfo.' . $key),
                    'values' => $values,
                ];
            });

        return [
            'id' => $this->id,
            'row_id' => $this->rowId,
            'name' => Arr::get($this->options, 'name', $this->name),
            'quantity' => $this->qty,
            'select_qty' => $this->qty,
            'description' => Arr::get($this->options, 'description'),
            'price' => $this->price,
            'price_label' => format_price($this->price),
            'original_price' => $originalPrice,
            'original_price_label' => format_price($originalPrice),
            'total_price' => $totalPrice,
            'total_price_label' => format_price($totalPrice),
            'tax_price' => $taxPrice,
            'tax_rate' => $this->getTaxRate(),
            'weight' => Arr::get($this->options, 'weight'),
            'image' => Arr::get($this->options, 'image'),
            'image_url' => RvMedia::getImageUrl(Arr::get($this->options, 'image'), 'thumb', false, RvMedia::getDefaultImage()),
            'options' => $options,
            'cart_options' => $this->options,
            'variation_attributes' => Arr::get($this->options, 'attributes'),
            'option_values' => $optionValues,
            'product_link' => Auth::check() && Auth::user()->hasPermission('products.edit') ? Arr::get($this->options, 'product_link') : '',
            'product_type' => Arr::get($this->options, 'product_type'),
        ];
    }
}
