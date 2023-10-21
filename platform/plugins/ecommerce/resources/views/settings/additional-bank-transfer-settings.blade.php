<x-core-setting::checkbox
    name="payment_bank_transfer_display_bank_info_at_the_checkout_success_page"
    :label="trans('plugins/ecommerce::ecommerce.setting.display_bank_info_at_the_checkout_success_page')"
    :checked="setting('payment_bank_transfer_display_bank_info_at_the_checkout_success_page', false)"
/>
