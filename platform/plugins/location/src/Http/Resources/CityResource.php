<?php

namespace Botble\Location\Http\Resources;

use Botble\Location\Models\City;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin City
 */
class CityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
        ];
    }
}
