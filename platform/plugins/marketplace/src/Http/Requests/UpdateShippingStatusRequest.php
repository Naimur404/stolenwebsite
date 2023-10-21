<?php

namespace Botble\Marketplace\Http\Requests;

use Botble\Ecommerce\Enums\ShippingStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UpdateShippingStatusRequest extends Request
{
    public function rules(): array
    {
        if (MarketplaceHelper::allowVendorManageShipping()) {
            return [
                'status' => Rule::in(ShippingStatusEnum::values()),
            ];
        }

        return [
            'status' => Rule::in([ShippingStatusEnum::ARRANGE_SHIPMENT, ShippingStatusEnum::READY_TO_BE_SHIPPED_OUT]),
        ];
    }
}
