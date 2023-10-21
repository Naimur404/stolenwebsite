@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        {!! Form::open(['url' => route('ecommerce.advanced-settings'), 'class' => 'main-setting-form']) !!}
            <x-core-setting::section
                :title="trans('plugins/ecommerce::ecommerce.advanced_settings')"
                :description="trans('plugins/ecommerce::ecommerce.setting.other_settings_description')"
            >
                <x-core-setting::on-off
                    name="shopping_cart_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_cart')"
                    :value="EcommerceHelper::isCartEnabled()"
                />

                <x-core-setting::on-off
                    name="wishlist_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_wishlist')"
                    :value="EcommerceHelper::isWishlistEnabled()"
                />

                <x-core-setting::on-off
                    name="compare_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_compare')"
                    :value="EcommerceHelper::isCompareEnabled()"
                />

                <x-core-setting::on-off
                    name="ecommerce_tax_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_tax')"
                    :value="EcommerceHelper::isTaxEnabled()"
                    class="trigger-input-option"
                    data-setting-container="#tax-settings"
                />

                <div id="tax-settings" @class(['mb-4 border rounded-top rounded-bottom p-3 bg-light', 'd-none' => ! EcommerceHelper::isTaxEnabled()])>
                    <x-core-setting::select
                        name="default_tax_rate"
                        :label="trans('plugins/ecommerce::ecommerce.setting.default_tax_rate')"
                        :options="[0 => trans('plugins/ecommerce::tax.select_tax')] + app(\Botble\Ecommerce\Repositories\Interfaces\TaxInterface::class)->pluck('title', 'id')"
                        :value="get_ecommerce_setting('default_tax_rate')"
                        :helper-text="trans('plugins/ecommerce::ecommerce.setting.default_tax_rate_description')"
                    />

                    <x-core-setting::on-off
                        name="display_product_price_including_taxes"
                        :label="trans('plugins/ecommerce::ecommerce.setting.display_product_price_including_taxes')"
                        :value="EcommerceHelper::isDisplayProductIncludingTaxes()"
                    />
                </div>

                <x-core-setting::on-off
                    name="order_tracking_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_order_tracking')"
                    :value="EcommerceHelper::isOrderTrackingEnabled()"
                />

                <x-core-setting::on-off
                    name="order_auto_confirmed"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_order_auto_confirmed')"
                    :value="EcommerceHelper::isOrderAutoConfirmedEnabled()"
                />

                <x-core-setting::on-off
                    name="review_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_review')"
                    :value="EcommerceHelper::isReviewEnabled()"
                    class="trigger-input-option"
                    data-setting-container=".review-settings-container"
                />

                <div @class(['review-settings-container mb-4 border rounded-top rounded-bottom p-3 bg-light', 'd-none' => ! EcommerceHelper::isReviewEnabled()])>
                    <x-core-setting::form-group>
                        <label class="text-title-field" for="review_max_file_size">{{ trans('plugins/ecommerce::ecommerce.setting.review.max_file_size') }}</label>
                        <div class="next-input--stylized">
                            <span class="next-input-add-on next-input__add-on--before">MB</span>
                            <input type="number" min="1" max="1024" name="review_max_file_size" class="next-input input-mask-number next-input--invisible" value="{{ EcommerceHelper::reviewMaxFileSize() }}">
                        </div>
                    </x-core-setting::form-group>

                    <x-core-setting::text-input
                        name="review_max_file_number"
                        :label="trans('plugins/ecommerce::ecommerce.setting.review.max_file_number')"
                        :value="EcommerceHelper::reviewMaxFileNumber()"
                        type="number"
                        min="1"
                        max="100"
                    />

                    <x-core-setting::on-off
                        name="only_allow_customers_purchased_to_review"
                        :label="trans('plugins/ecommerce::ecommerce.setting.only_allow_customers_purchased_to_review')"
                        :value="EcommerceHelper::onlyAllowCustomersPurchasedToReview()"
                    />
                </div>

                <x-core-setting::on-off
                    name="enable_quick_buy_button"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_quick_buy_button')"
                    :value="EcommerceHelper::isQuickBuyButtonEnabled()"
                />

                <x-core-setting::radio
                    name="quick_buy_target_page"
                    :label="trans('plugins/ecommerce::ecommerce.setting.quick_buy_target')"
                    :options="[
                        'checkout' => trans('plugins/ecommerce::ecommerce.setting.checkout_page'),
                        'cart' => trans('plugins/ecommerce::ecommerce.setting.cart_page'),
                    ]"
                    :value="EcommerceHelper::getQuickBuyButtonTarget()"
                />

                <x-core-setting::on-off
                    name="zip_code_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.zip_code_enabled')"
                    :value="EcommerceHelper::isZipCodeEnabled()"
                />

                <x-core-setting::on-off
                    name="billing_address_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.billing_address_enabled')"
                    :value="EcommerceHelper::isBillingAddressEnabled()"
                />

                <x-core-setting::on-off
                    name="verify_customer_email"
                    :label="trans('plugins/ecommerce::ecommerce.setting.verify_customer_email')"
                    :value="EcommerceHelper::isEnableEmailVerification()"
                />

                @if (is_plugin_active('captcha'))
                    <x-core-setting::on-off
                        name="enable_recaptcha_in_register_page"
                        :label="trans('plugins/ecommerce::ecommerce.setting.enable_recaptcha_in_register_page')"
                        :value="get_ecommerce_setting('enable_recaptcha_in_register_page', false)"
                        :helper-text="trans('plugins/ecommerce::ecommerce.setting.enable_recaptcha_in_register_page_description')"
                    />

                    <x-core-setting::checkbox
                        name="enable_math_captcha_in_register_page"
                        :label="trans('plugins/ecommerce::ecommerce.setting.enable_math_captcha_in_register_page')"
                        :checked="get_ecommerce_setting('enable_math_captcha_in_register_page', false)"
                    />
                @endif

                <x-core-setting::on-off
                    name="enable_guest_checkout"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_guest_checkout')"
                    :value="EcommerceHelper::isEnabledGuestCheckout()"
                />

                <x-core-setting::radio
                    name="how_to_display_product_variation_images"
                    :label="trans('plugins/ecommerce::ecommerce.setting.how_to_display_product_variation_images')"
                    :options="[
                        'only_variation_images' => trans('plugins/ecommerce::ecommerce.setting.only_variation_images'),
                        'variation_images_and_main_product_images' => trans('plugins/ecommerce::ecommerce.setting.variation_images_and_main_product_images'),
                    ]"
                    :value="get_ecommerce_setting('how_to_display_product_variation_images', 'only_variation_images')"
                />

                <x-core-setting::on-off
                    name="show_number_of_products"
                    :label="trans('plugins/ecommerce::ecommerce.setting.show_number_of_products')"
                    :value="EcommerceHelper::showNumberOfProductsInProductSingle()"
                />

                <x-core-setting::on-off
                    name="show_out_of_stock_products"
                    :label="trans('plugins/ecommerce::ecommerce.setting.show_out_of_stock_products')"
                    :value="EcommerceHelper::showOutOfStockProducts()"
                />

                <div class="mb-3 form-group">
                    <label class="text-title-field" for="minimum_order_amount">{{ trans('plugins/ecommerce::ecommerce.setting.minimum_order_amount', ['currency' => get_application_currency()->title]) }}</label>
                    <div class="next-input--stylized">
                        <span class="next-input-add-on next-input__add-on--before unit-item-price-label">{{ get_application_currency()->symbol }}</span>
                        <input type="number" name="minimum_order_amount" class="next-input input-mask-number next-input--invisible" data-thousands-separator="{{ EcommerceHelper::getThousandSeparatorForInputMask() }}" data-decimal-separator="{{ EcommerceHelper::getDecimalSeparatorForInputMask() }}" value="{{ get_ecommerce_setting('minimum_order_amount', 0) }}">
                    </div>
                </div>

                <x-core-setting::form-group>
                    <input type="hidden" name="mandatory_form_fields_at_checkout[]">
                    <label for="mandatory_form_fields_at_checkout" class="text-title-field">{{ trans('plugins/ecommerce::ecommerce.setting.mandatory_form_fields_at_checkout') }}</label>
                    @foreach(EcommerceHelper::getMandatoryFieldsAtCheckout() as $key => $value)
                        <label class="me-2">
                            <input type="checkbox" name="mandatory_form_fields_at_checkout[]" value="{{ $key }}" @checked(in_array($key, EcommerceHelper::getEnabledMandatoryFieldsAtCheckout()))>
                            {{ $value }}
                        </label>
                    @endforeach
                </x-core-setting::form-group>

                <x-core-setting::form-group>
                    <input type="hidden" name="hide_form_fields_at_checkout[]">
                    <label for="hide_form_fields_at_checkout" class="text-title-field">{{ trans('plugins/ecommerce::ecommerce.setting.hide_form_fields_at_checkout') }}</label>
                    @foreach(EcommerceHelper::getMandatoryFieldsAtCheckout() as $key => $value)
                        <label class="me-2">
                            <input type="checkbox" name="hide_form_fields_at_checkout[]" value="{{ $key }}" @checked(in_array($key, EcommerceHelper::getHiddenFieldsAtCheckout()))>
                            {{ $value }}
                        </label>
                    @endforeach
                </x-core-setting::form-group>

                <x-core-setting::on-off
                    name="display_tax_fields_at_checkout_page"
                    :label="trans('plugins/ecommerce::ecommerce.setting.display_tax_fields_at_checkout_page')"
                    :value="EcommerceHelper::isDisplayTaxFieldsAtCheckoutPage()"
                />

                <x-core-setting::form-group>
                    <label class="text-title-field" for="available_countries">{{ trans('plugins/ecommerce::ecommerce.setting.available_countries') }}</label>
                    <label>
                        <input type="checkbox" class="check-all" data-set=".available-countries">
                        {{ trans('plugins/ecommerce::ecommerce.setting.all') }}
                    </label>
                    <div class="form-group form-group-no-margin">
                        <div class="multi-choices-widget list-item-checkbox">
                            <ul>
                                @foreach (\Botble\Base\Supports\Helper::countries() as $key => $item)
                                    <li>
                                        <input
                                            type="checkbox"
                                            class="styled available-countries"
                                            name="available_countries[]"
                                            value="{{ $key }}"
                                            id="available-countries-item-{{ $key }}"
                                            @checked(in_array($key, array_keys(EcommerceHelper::getAvailableCountries())))
                                        >
                                        <label for="available-countries-item-{{ $key }}">{{ $item }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </x-core-setting::form-group>

                @if (is_plugin_active('location'))
                    <x-core-setting::on-off
                        name="load_countries_states_cities_from_location_plugin"
                        :label="trans('plugins/ecommerce::ecommerce.setting.load_countries_states_cities_from_location_plugin')"
                        :value="EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()"
                        :helperText="trans('plugins/ecommerce::ecommerce.setting.load_countries_states_cities_from_location_plugin_placeholder')"
                    />
                @endif

                <x-core-setting::on-off
                    name="enable_customer_recently_viewed_products"
                    :label="trans('plugins/ecommerce::ecommerce.setting.recently_viewed.enable')"
                    :value="EcommerceHelper::isEnabledCustomerRecentlyViewedProducts()"
                    class="trigger-input-option"
                    data-setting-container=".recently-viewed-products-settings-container"
                />

                <div @class(['recently-viewed-products-settings-container mb-4 border rounded-top rounded-bottom p-3 pb-0 bg-light', 'd-none' => !EcommerceHelper::isEnabledCustomerRecentlyViewedProducts()])>
                    <x-core-setting::text-input
                        name="max_customer_recently_viewed_products"
                        :label="trans('plugins/ecommerce::ecommerce.setting.recently_viewed.max')"
                        type="number"
                        :value="EcommerceHelper::maxCustomerRecentlyViewedProducts()"
                        min="0"
                        max="100"
                        :helper-text="trans('plugins/ecommerce::ecommerce.setting.recently_viewed.max_helper')"
                    />
                </div>

                <x-core-setting::on-off
                    name="is_enabled_product_options"
                    :label="trans('plugins/ecommerce::ecommerce.setting.is_enabled_product_options')"
                    :value="EcommerceHelper::isEnabledProductOptions()"
                />

                <x-core-setting::on-off
                    name="use_city_field_as_field_text"
                    :label="trans('plugins/ecommerce::ecommerce.setting.use_city_field_as_field_text')"
                    :value="get_ecommerce_setting('use_city_field_as_field_text', false)"
                />
            </x-core-setting::section>

            <x-core-setting::section
                :title="trans('plugins/ecommerce::ecommerce.setting.shipping')"
                :description="trans('plugins/ecommerce::ecommerce.setting.shipping_description')"
            >
                <x-core-setting::on-off
                    name="hide_other_shipping_options_if_it_has_free_shipping"
                    :label="trans('plugins/ecommerce::ecommerce.setting.hide_other_shipping_options_if_it_has_free_shipping')"
                    :value="get_ecommerce_setting('hide_other_shipping_options_if_it_has_free_shipping', false)"
                />
            </x-core-setting::section>

            <x-core-setting::section
                :title="trans('plugins/ecommerce::ecommerce.setting.company_settings')"
                :description="trans('plugins/ecommerce::ecommerce.setting.company_settings_description')"
            >
                <x-core-setting::text-input
                    name="company_name_for_invoicing"
                    :label="trans('plugins/ecommerce::ecommerce.setting.company_name')"
                    :value="get_ecommerce_setting('company_name_for_invoicing') ?: get_ecommerce_setting('store_name')"
                />

                <x-core-setting::text-input
                    name="company_address_for_invoicing"
                    :label="trans('plugins/ecommerce::ecommerce.setting.company_address')"
                    :value="get_ecommerce_setting('company_address_for_invoicing') ?: implode(', ', array_filter([get_ecommerce_setting('store_address'), get_ecommerce_setting('store_city'), get_ecommerce_setting('store_state'), EcommerceHelper::getCountryNameById(get_ecommerce_setting('store_country'))]))"
                />

                @if (EcommerceHelper::isZipCodeEnabled())
                    <x-core-setting::text-input
                        name="company_zipcode_for_invoicing"
                        :label="trans('plugins/ecommerce::ecommerce.setting.company_zipcode')"
                        :value="get_ecommerce_setting('company_zipcode_for_invoicing') ?: get_ecommerce_setting('store_zip_code')"
                    />
                @endif

                <x-core-setting::text-input
                    name="company_email_for_invoicing"
                    :label="trans('plugins/ecommerce::ecommerce.setting.company_email')"
                    :value="get_ecommerce_setting('company_email_for_invoicing') ?: get_ecommerce_setting('store_email')"
                />

                <x-core-setting::text-input
                    name="company_phone_for_invoicing"
                    :label="trans('plugins/ecommerce::ecommerce.setting.company_phone')"
                    :value="get_ecommerce_setting('company_phone_for_invoicing') ?: get_ecommerce_setting('store_phone')"
                />

                <x-core-setting::text-input
                    name="company_tax_id_for_invoicing"
                    :label="trans('plugins/ecommerce::ecommerce.setting.company_tax_id')"
                    :value="get_ecommerce_setting('company_tax_id_for_invoicing') ?: get_ecommerce_setting('store_vat_number')"
                />

                <x-core-setting::form-group>
                    <label class="text-title-field" for="company_logo_for_invoicing">{{ trans('plugins/ecommerce::ecommerce.setting.company_logo') }}</label>
                    {!! Form::mediaImage('company_logo_for_invoicing', get_ecommerce_setting('company_logo_for_invoicing') ?: (theme_option('logo_in_invoices') ?: theme_option('logo')), ['allow_thumb' => false]) !!}
                </x-core-setting::form-group>

                <x-core-setting::on-off
                    name="using_custom_font_for_invoice"
                    :label="trans('plugins/ecommerce::ecommerce.setting.using_custom_font_for_invoice')"
                    :value="get_ecommerce_setting('using_custom_font_for_invoice', false)"
                    class="trigger-input-option"
                    data-setting-container=".custom-font-settings-container"
                />

                <div @class(['custom-font-settings-container mb-4 border rounded-top rounded-bottom p-3 bg-light', 'd-none' => ! get_ecommerce_setting('using_custom_font_for_invoice', false)])>
                    <x-core-setting::form-group>
                        <label class="text-title-field" for="invoice_font_family">{{ trans('plugins/ecommerce::ecommerce.setting.invoice_font_family') }}</label>
                        {!! Form::googleFonts('invoice_font_family', get_ecommerce_setting('invoice_font_family')) !!}
                    </x-core-setting::form-group>
                </div>

                <x-core-setting::on-off
                    name="invoice_support_arabic_language"
                    :label="trans('plugins/ecommerce::ecommerce.setting.invoice_support_arabic_language')"
                    :value="get_ecommerce_setting('invoice_support_arabic_language', false)"
                />

                <x-core-setting::on-off
                    name="enable_invoice_stamp"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_invoice_stamp')"
                    :value="get_ecommerce_setting('enable_invoice_stamp', true)"
                />

                <x-core-setting::text-input
                    name="invoice_code_prefix"
                    :label="trans('plugins/ecommerce::ecommerce.setting.invoice_code_prefix')"
                    :value="get_ecommerce_setting('invoice_code_prefix', 'INV-')"
                />

                <x-core-setting::on-off
                    name="disable_order_invoice_until_order_confirmed"
                    :label="trans('plugins/ecommerce::ecommerce.setting.disable_order_invoice_until_order_confirmed')"
                    :value="EcommerceHelper::disableOrderInvoiceUntilOrderConfirmed()"
                />
            </x-core-setting::section>

            <x-core-setting::section
                :title="trans('plugins/ecommerce::ecommerce.setting.search_products')"
                :description="trans('plugins/ecommerce::ecommerce.setting.search_products_description')"
            >
                <x-core-setting::on-off
                    name="search_for_an_exact_phrase"
                    :label="trans('plugins/ecommerce::ecommerce.setting.search_for_an_exact_phrase')"
                    :value="get_ecommerce_setting('search_for_an_exact_phrase', false)"
                />

                <x-core-setting::form-group>
                    <label class="text-title-field" for="search_products_by">{{ trans('plugins/ecommerce::ecommerce.setting.search_products_by') }}</label>
                    <div class="form-group form-group-no-margin">
                        <div class="multi-choices-widget list-item-checkbox">
                            <ul>
                                @foreach ([
                                    'name' => trans('plugins/ecommerce::products.form.name'),
                                    'sku' => trans('plugins/ecommerce::products.sku'),
                                    'variation_sku' => trans('plugins/ecommerce::products.variation_sku'),
                                    'description' => trans('plugins/ecommerce::products.form.description'),
                                    'brand' => trans('plugins/ecommerce::products.form.brand'),
                                    'tag' => trans('plugins/ecommerce::products.form.tags'),
                                ] as $key => $item)
                                    <li>
                                        <input
                                            type="checkbox"
                                            class="styled"
                                            name="search_products_by[]"
                                            value="{{ $key }}"
                                            id="search_products_by-item-{{ $key }}"
                                            @checked(in_array($key, EcommerceHelper::getProductsSearchBy()))
                                        >
                                        <label for="search_products_by-item-{{ $key }}">{{ $item }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </x-core-setting::form-group>

                <x-core-setting::on-off
                    name="enable_filter_products_by_brands"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_filter_products_by_brands')"
                    :value="EcommerceHelper::isEnabledFilterProductsByBrands()"
                />

                <x-core-setting::on-off
                    name="enable_filter_products_by_tags"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_filter_products_by_tags')"
                    :value="EcommerceHelper::isEnabledFilterProductsByTags()"
                />

                <x-core-setting::on-off
                    name="enable_filter_products_by_attributes"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_filter_products_by_attributes')"
                    :value="EcommerceHelper::isEnabledFilterProductsByAttributes()"
                />
            </x-core-setting::section>

            <x-core-setting::section
                :title="trans('plugins/ecommerce::ecommerce.setting.webhook')"
                :description="trans('plugins/ecommerce::ecommerce.setting.webhook_description')"
            >
                <x-core-setting::text-input
                    name="order_placed_webhook_url"
                    :label="trans('plugins/ecommerce::ecommerce.setting.order_placed_webhook_url')"
                    :value="get_ecommerce_setting('order_placed_webhook_url')"
                    placeholder="https://..."
                />
            </x-core-setting::section>

            <x-core-setting::section
                :title="trans('plugins/ecommerce::ecommerce.setting.return_request')"
                :description="trans('plugins/ecommerce::ecommerce.setting.return_request_description')"
            >
                <x-core-setting::on-off
                    name="is_enabled_order_return"
                    :label="trans('plugins/ecommerce::ecommerce.setting.is_enabled_order_return')"
                    :value="EcommerceHelper::isOrderReturnEnabled()"
                    class="trigger-input-option"
                    data-setting-container=".order-returns-settings-container"
                />

                <div @class(['order-returns-settings-container mb-4 border rounded-top rounded-bottom p-3 bg-light', 'd-none' => ! EcommerceHelper::isOrderReturnEnabled()])>
                    <x-core-setting::on-off
                        name="can_custom_return_product_quantity"
                        :label="trans('plugins/ecommerce::ecommerce.setting.allow_partial_return')"
                        :value="EcommerceHelper::allowPartialReturn()"
                        :helper-text="trans('plugins/ecommerce::ecommerce.setting.allow_partial_return_description')"
                    />

                    <x-core-setting::text-input
                        name="returnable_days"
                        :label="trans('plugins/ecommerce::ecommerce.setting.returnable_days')"
                        type="number"
                        min="0"
                        :value="get_ecommerce_setting('returnable_days')"
                        :placeholder="trans('plugins/ecommerce::ecommerce.setting.returnable_days')"
                    />
                </div>
            </x-core-setting::section>

            <x-core-setting::section
                :title="trans('plugins/ecommerce::ecommerce.setting.digital_product')"
            >
                <x-core-setting::on-off
                    name="is_enabled_support_digital_products"
                    :label="trans('plugins/ecommerce::ecommerce.setting.digital_product_title')"
                    :value="EcommerceHelper::isEnabledSupportDigitalProducts()"
                    class="trigger-input-option"
                    data-setting-container=".digital-products-settings-container"
                />

                <div @class(['digital-products-settings-container mb-4 border rounded-top rounded-bottom p-3 bg-light', 'd-none' => ! EcommerceHelper::isEnabledSupportDigitalProducts()])>
                    <x-core-setting::on-off
                        name="allow_guest_checkout_for_digital_products"
                        :label="trans('plugins/ecommerce::ecommerce.setting.allow_guest_checkout_for_digital_products')"
                        :value="EcommerceHelper::allowGuestCheckoutForDigitalProducts()"
                    />
                </div>
            </x-core-setting::section>

            <div class="flexbox-annotated-section" style="border: none">
                <div class="flexbox-annotated-section-annotation">&nbsp;</div>
                <div class="flexbox-annotated-section-content">
                    <button class="btn btn-info" type="submit">{{ trans('plugins/ecommerce::currency.save_settings') }}</button>
                </div>
            </div>
        {!! Form::close() !!}
    </div>

    {!! $jsValidation !!}
@endsection
