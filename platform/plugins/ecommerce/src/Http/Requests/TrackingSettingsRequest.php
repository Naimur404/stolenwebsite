<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class TrackingSettingsRequest extends Request
{
    public function rules(): array
    {
        return [
            'facebook_pixel_enabled' => 'nullable|in:0,1',
            'facebook_pixel_id' => 'nullable|required_if:facebook_pixel_enabled,1|string|max:120',
            'google_tag_manager_enabled' => 'nullable|in:0,1',
            'google_tag_manager_code' => 'nullable|required_if:google_tag_manager_enabled,1|string|max:400',
        ];
    }
}
