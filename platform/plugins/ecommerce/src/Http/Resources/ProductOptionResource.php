<?php

namespace Botble\Ecommerce\Http\Resources;

use Botble\Ecommerce\Models\Option;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Option
 */
class ProductOptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'order' => $this->order,
            'required' => $this->required,
            'option_type' => (new $this->option_type())->view(),
            'values' => ProductOptionValueResource::collection($this->values),
        ];
    }
}
