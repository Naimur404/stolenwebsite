<?php

namespace Botble\Location\Http\Resources;

use Botble\Location\Models\Country;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Country
 */
class CountryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
        ];
    }
}
