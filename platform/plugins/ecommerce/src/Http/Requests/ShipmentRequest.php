<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ShipmentRequest extends Request
{
    public function rules(): array
    {
        return [
            'tracking_id' => 'nullable|max:120',
            'shipping_company_name' => 'nullable|string|max:120',
            'tracking_link' => 'nullable|url|max:190',
            'estimate_date_shipped' => 'nullable|date|date_format:' . config('core.base.general.date_format.date'),
            'note' => 'nullable|string|max:120',
        ];
    }
}
