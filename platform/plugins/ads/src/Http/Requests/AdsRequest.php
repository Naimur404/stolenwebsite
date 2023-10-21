<?php

namespace Botble\Ads\Http\Requests;

use Botble\Ads\Facades\AdsManager;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class AdsRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'key' => 'required|max:120|unique:ads,key,' . $this->route('ads.id'),
            'location' => 'required|' . Rule::in(array_keys(AdsManager::getLocations())),
            'order' => 'required|integer|min:0|max:127',
            'status' => Rule::in(BaseStatusEnum::values()),
            'expired_at' => 'required|date',
        ];
    }
}
