<?php

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Setting\Facades\Setting;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        try {
            if (get_ecommerce_setting('make_phone_field_at_the_checkout_optional')) {
                Setting::set(
                    'ecommerce_mandatory_form_fields_at_checkout',
                    json_encode(['email', 'country', 'state', 'city', 'address'])
                )->save();
            }
        } catch (Throwable) {}
    }

    public function down(): void
    {
        Setting::set(
            'ecommerce_make_phone_field_at_the_checkout_optional',
            ! in_array('phone', EcommerceHelper::getEnabledMandatoryFieldsAtCheckout())
        )->save();
    }
};
