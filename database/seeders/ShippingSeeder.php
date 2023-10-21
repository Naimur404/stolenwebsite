<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Enums\ShippingRuleTypeEnum;
use Botble\Ecommerce\Models\Shipping;
use Botble\Ecommerce\Models\ShippingRule;
use Botble\Ecommerce\Models\ShippingRuleItem;

class ShippingSeeder extends BaseSeeder
{
    public function run(): void
    {
        Shipping::query()->truncate();
        ShippingRule::query()->truncate();
        ShippingRuleItem::query()->truncate();

        $shipping = Shipping::query()->create(['title' => 'All']);

        ShippingRule::query()->create([
            'name' => 'Free delivery',
            'shipping_id' => $shipping->id,
            'type' => ShippingRuleTypeEnum::BASED_ON_PRICE,
            'from' => 0,
            'to' => null,
            'price' => 0,
        ]);
    }
}
