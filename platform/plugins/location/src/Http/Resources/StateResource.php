<?php

namespace Botble\Location\Http\Resources;

use Botble\Location\Models\State;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin State
 */
class StateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
        ];
    }
}
