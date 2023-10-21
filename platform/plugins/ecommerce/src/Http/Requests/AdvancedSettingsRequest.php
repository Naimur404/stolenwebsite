<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Helper;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class AdvancedSettingsRequest extends Request
{
    public function rules(): array
    {
        return [
            'shopping_cart_enabled' => 'nullable|in:0,1',
            'wishlist_enabled' => 'nullable|in:0,1',
            'compare_enabled' => 'nullable|in:0,1',
            'ecommerce_tax_enabled' => 'nullable|in:0,1',
            'default_tax_rate' => 'nullable|integer|min:0',
            'display_product_price_including_taxes' => 'nullable|in:0,1',
            'order_tracking_enabled' => 'nullable|in:0,1',
            'order_auto_confirmed' => 'nullable|in:0,1',
            'review_enabled' => 'nullable|in:0,1',
            'review_max_file_size' => 'nullable|required_if:review_enabled,1|numeric|min:1',
            'review_max_file_number' => 'nullable|required_if:review_enabled,1|integer|min:1',
            'only_allow_customers_purchased_to_review' => 'nullable|in:0,1',
            'enable_quick_buy_button' => 'nullable|in:0,1',
            'quick_buy_target_page' => 'nullable|required_if:enable_quick_buy_button,1|in:checkout,cart',
            'zip_code_enabled' => 'nullable|in:0,1',
            'billing_address_enabled' => 'nullable|in:0,1',
            'verify_customer_email' => 'nullable|in:0,1',
            'enable_recaptcha_in_register_page' => 'nullable|in:0,1',
            'enable_math_captcha_in_register_page' => 'nullable|in:0,1',
            'enable_guest_checkout' => 'nullable|in:0,1',
            'how_to_display_product_variation_images' => 'in:only_variation_images,variation_images_and_main_product_images',
            'show_number_of_products' => 'nullable|in:0,1',
            'show_out_of_stock_products' => 'nullable|in:0,1',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'mandatory_form_fields_at_checkout' => 'sometimes|array',
            'mandatory_form_fields_at_checkout.*' => ['nullable', 'string', Rule::in(array_keys(EcommerceHelper::getMandatoryFieldsAtCheckout()))],
            'hide_form_fields_at_checkout' => 'sometimes|array',
            'hide_form_fields_at_checkout.*' => ['nullable', 'string', Rule::in(array_keys(EcommerceHelper::getMandatoryFieldsAtCheckout()))],
            'available_countries' => 'sometimes|array',
            'available_countries.*' => ['string', Rule::in(array_keys(Helper::countries()))],
            'load_countries_states_cities_from_location_plugin' => 'nullable|in:0,1',
            'enable_customer_recently_viewed_products' => 'nullable|in:0,1',
            'max_customer_recently_viewed_products' => 'nullable|required_if:enable_customer_recently_viewed_products,1|integer|min:1',
            'is_enabled_product_options' => 'nullable|in:0,1',
            'hide_other_shipping_options_if_it_has_free_shipping' => 'nullable|in:0,1',
            'company_name_for_invoicing' => 'nullable|string|max:120',
            'company_address_for_invoicing' => 'nullable|string|max:255',
            'company_email_for_invoicing' => 'nullable|email',
            'company_phone_for_invoicing' => 'nullable|' . BaseHelper::getPhoneValidationRule(),
            'company_tax_id_for_invoicing' => 'nullable|string|max:120',
            'company_logo_for_invoicing' => 'nullable|string|max:255',
            'using_custom_font_for_invoice' => 'nullable|in:0,1',
            'invoice_support_arabic_language' => 'nullable|in:0,1',
            'enable_invoice_stamp' => 'nullable|in:0,1',
            'invoice_code_prefix' => 'nullable|string|max:120',
            'disable_order_invoice_until_order_confirmed' => 'nullable|in:0,1',
            'search_for_an_exact_phrase' => 'nullable|in:0,1',
            'search_products_by' => 'required|array',
            'search_products_by.*' => 'required|in:name,sku,variation_sku,description,brand,tag',
            'order_placed_webhook_url' => 'nullable|url',
            'is_enabled_order_return' => 'nullable|in:0,1',
            'can_custom_return_product_quantity' => 'nullable|in:0,1',
            'returnable_days' => 'nullable|integer|min:1',
            'is_enabled_support_digital_products' => 'nullable|in:0,1',
            'allow_guest_checkout_for_digital_products' => 'nullable|in:0,1',
        ];
    }
}
