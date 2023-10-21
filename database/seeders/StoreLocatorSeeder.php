<?php

namespace Database\Seeders;

use Botble\Ecommerce\Models\StoreLocator;
use Botble\Setting\Facades\Setting;
use Illuminate\Database\Seeder;

class StoreLocatorSeeder extends Seeder
{
    public function run(): void
    {
        StoreLocator::query()->truncate();

        $storeLocator = StoreLocator::query()->create([
            'name' => 'Farmart',
            'email' => 'sales@botble.com',
            'phone' => '1800979769',
            'address' => '502 New Street',
            'state' => 'Brighton VIC',
            'city' => 'Brighton VIC',
            'country' => 'AU',
            'is_primary' => 1,
            'is_shipping_location' => 1,
        ]);

        Setting::delete([
            'ecommerce_store_name',
            'ecommerce_store_phone',
            'ecommerce_store_address',
            'ecommerce_store_state',
            'ecommerce_store_city',
            'ecommerce_store_country',
        ]);

        Setting::set([
            'ecommerce_store_name' => $storeLocator->name,
            'ecommerce_store_phone' => $storeLocator->phone,
            'ecommerce_store_address' => $storeLocator->address,
            'ecommerce_store_state' => $storeLocator->state,
            'ecommerce_store_city' => $storeLocator->city,
            'ecommerce_store_country' => $storeLocator->country,
        ])->save();
    }
}
