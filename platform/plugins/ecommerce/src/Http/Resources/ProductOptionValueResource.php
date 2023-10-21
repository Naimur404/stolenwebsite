<?php

namespace Botble\Ecommerce\Http\Resources;

use Botble\Ecommerce\Models\OptionValue;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OptionValue
 */
class ProductOptionValueResource extends JsonResource
{
    public function toArray($request): array
    {
        $formatPrice = $this->price ? (' + ' . $this->format_price) : '';

        return [
            'id' => $this->id,
            'name' => $this->option_value,
            'option_value' => $this->option_value,
            'order' => $this->order,
            'affect_type' => $this->affect_type,
            'affect_price' => $this->affect_price,
            'title' => $this->option_value . $formatPrice,
            'format_price' => $formatPrice,
        ];
    }
}
