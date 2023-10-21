<?php

namespace Botble\Ecommerce\Http\Resources;

use Botble\Ecommerce\Models\ProductCategory;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductCategory
 */
class ProductCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
