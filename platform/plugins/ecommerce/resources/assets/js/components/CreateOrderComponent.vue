<template>
    <div class='flexbox-grid no-pd-none'>
        <div class='flexbox-content'>
            <div class='wrapper-content'>
                <div class='pd-all-20'>
                    <label class='title-product-main text-no-bold'>{{ __('order.order_information') }}</label>
                </div>
                <div class='pd-all-10-20 border-top-title-main'>
                    <div class='clearfix'>
                        <div class='table-wrapper p-none mb20 ps-relative z-index-4'
                             :class="{'loading-skeleton': checking}" v-if='child_products.length'>
                            <table class='table table-bordered'>
                                <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ __('order.product_name') }}</th>
                                    <th>{{ __('order.price') }}</th>
                                    <th width='90'>{{ __('order.quantity') }}</th>
                                    <th>{{ __('order.total') }}</th>
                                    <th>{{ __('order.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for='(variant, vKey) in child_products' v-bind:key="variant.id + '-' + vKey">
                                    <td>
                                        <div class='wrap-img vertical-align-m-i'>
                                            <img class='thumb-image' :src='variant.image_url' :alt='variant.name'
                                                 width='50'>
                                        </div>
                                    </td>
                                    <td>
                                        <a class='hover-underline pre-line' :href='variant.product_link'
                                           target='_blank'>{{ variant.name }}</a>
                                        <p class='type-subdued' v-if='variant.variation_attributes'>
                                            <span class='small'>{{ variant.variation_attributes }}</span>
                                        </p>
                                        <ul v-if='variant.option_values && Object.keys(variant.option_values).length'
                                            class='small'>
                                            <li>
                                                <span>{{ __('order.price') }}:</span>
                                                <span>{{ variant.original_price_label }}</span>
                                            </li>
                                            <li v-for='option in variant.option_values' v-bind:key='option.id'>
                                                <span>{{ option.title }}:</span>
                                                <span v-for='value in option.values' v-bind:key='value.id'>
                                                        {{ value.value }} <strong>+{{ value.price_label }}</strong>
                                                    </span>
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <span>{{ variant.price_label }}</span>
                                    </td>
                                    <td>
                                        <input class='form-control' :value='variant.select_qty' type='number' min='1'
                                               @input='handleChangeQuantity($event, variant, vKey)'>
                                    </td>
                                    <td class='text-center'>
                                        {{ variant.total_price_label }}
                                    </td>
                                    <td class='text-center'>
                                        <a href='#' @click='handleRemoveVariant($event, variant, vKey)'>
                                            <svg class='svg-next-icon svg-next-icon-size-12'>
                                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'
                                                     enable-background='new 0 0 24 24'>
                                                    <path
                                                        d='M19.5 22c-.2 0-.5-.1-.7-.3L12 14.9l-6.8 6.8c-.2.2-.4.3-.7.3-.2 0-.5-.1-.7-.3l-1.6-1.6c-.1-.2-.2-.4-.2-.6 0-.2.1-.5.3-.7L9.1 12 2.3 5.2C2.1 5 2 4.8 2 4.5c0-.2.1-.5.3-.7l1.6-1.6c.2-.1.4-.2.6-.2.3 0 .5.1.7.3L12 9.1l6.8-6.8c.2-.2.4-.3.7-.3.2 0 .5.1.7.3l1.6 1.6c.1.2.2.4.2.6 0 .2-.1.5-.3.7L14.9 12l6.8 6.8c.2.2.3.4.3.7 0 .2-.1.5-.3.7l-1.6 1.6c-.2.1-.4.2-.6.2z'></path>
                                                </svg>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class='box-search-advance product'>
                            <div>
                                <input type='text' class='next-input textbox-advancesearch product'
                                       :placeholder="__('order.search_or_create_new_product')"
                                       @click='loadListProductsAndVariations()'
                                       @keyup='handleSearchProduct($event.target.value)'>
                            </div>
                            <div class='panel panel-default'
                                 :class='{ active: list_products, hidden : hidden_product_search_panel }'>
                                <div class='panel-body'>
                                    <div class='box-search-advance-head' v-ec-modal.add-product-item>
                                        <img width='30'
                                             src='/vendor/core/plugins/ecommerce/images/next-create-custom-line-item.svg'
                                             alt='icon'>
                                        <span class='ml10'>{{ __('order.create_a_new_product') }}</span>
                                    </div>
                                    <div class='list-search-data'>
                                        <div class='has-loading' v-show='loading'>
                                            <i class='fa fa-spinner fa-spin'></i>
                                        </div>
                                        <ul class='clearfix' v-show='!loading'>
                                            <li v-for='product_item in list_products.data'
                                                :class="{
                                                    'item-selectable' : !product_item.variations.length,
                                                    'item-not-selectable' : product_item.variations.length,
                                                }"
                                                v-bind:key='product_item.id'>
                                                <div class='wrap-img inline_block vertical-align-t float-start'>
                                                    <img class='thumb-image' :src='product_item.image_url'
                                                         :alt='product_item.name'>
                                                </div>
                                                <div class='inline_block ml10 mt10 ws-nm'
                                                     style='width: calc(100% - 50px);'>
                                                    <ProductAction :ref="'product_actions_' + product_item.id"
                                                                   :product='product_item'
                                                                   @select-product='selectProductVariant'></ProductAction>
                                                </div>
                                                <div v-if='product_item.variations.length'>
                                                    <ul>
                                                        <li class='product-variant'
                                                            v-for='variation in product_item.variations'
                                                            v-bind:key='variation.id'>
                                                            <ProductAction :product='variation'
                                                                           @select-product='selectProductVariant'></ProductAction>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </li>
                                            <li v-if='list_products.data && list_products.data.length === 0'>
                                                <span>{{ __('order.no_products_found') }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class='panel-footer'
                                     v-if='(list_products.links && list_products.links.next) || (list_products.links && list_products.links.prev)'>
                                    <div class='btn-group float-end'>
                                        <button type='button'
                                                @click='loadListProductsAndVariations((list_products.links.prev ? list_products.meta.current_page - 1 : list_products.meta.current_page), true)'
                                                :class="{ 'btn btn-secondary': list_products.meta.current_page !== 1, 'btn btn-secondary disable': list_products.meta.current_page === 1}"
                                                :disabled='list_products.meta.current_page === 1'>
                                            <svg role='img'
                                                 class='svg-next-icon svg-next-icon-size-16 svg-next-icon-rotate-180'>
                                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'>
                                                    <path d='M6 4l9 8-9 8 2 2 11-10L8 2 6 4' fill='currentColor'></path>
                                                </svg>
                                            </svg>
                                        </button>
                                        <button type='button'
                                                @click='loadListProductsAndVariations((list_products.links.next ? list_products.meta.current_page + 1 : list_products.meta.current_page), true)'
                                                :class="{ 'btn btn-secondary': list_products.links.next, 'btn btn-secondary disable': !list_products.links.next }"
                                                :disabled='!list_products.links.next'>
                                            <svg role='img' class='svg-next-icon svg-next-icon-size-16'>
                                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'>
                                                    <path d='M6 4l9 8-9 8 2 2 11-10L8 2 6 4' fill='currentColor'></path>
                                                </svg>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class='clearfix'></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class='pd-all-10-20 p-none-t'>
                    <div class='row'>
                        <div class='col-sm-6'>
                            <div class='form-group mb-3'>
                                <label class='text-title-field' for='txt-note'>{{ __('order.note') }}</label>
                                <textarea class='ui-text-area textarea-auto-height' id='txt-note' rows='2'
                                          :placeholder="__('order.note_for_order')" v-model='note'></textarea>
                            </div>
                        </div>
                        <div class='col-sm-6'>
                            <div class='table-wrap'>
                                <table class='table-normal table-none-border table-color-gray-text text-end'>
                                    <thead>
                                    <tr>
                                        <td></td>
                                        <td width='120'></td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class='color-subtext'>{{ __('order.sub_amount') }}</td>
                                        <td>
                                            <div>
                                                <span v-if='checking' class='spinner-grow spinner-grow-sm' role='status'
                                                      aria-hidden='true'></span>
                                                <span class='fw-bold fs-6'>{{ child_sub_amount_label }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='color-subtext'>{{ __('order.tax_amount') }}</td>
                                        <td>
                                            <div>
                                                <span v-if='checking' class='spinner-grow spinner-grow-sm' role='status'
                                                      aria-hidden='true'></span>
                                                <span class='fw-bold'>{{ child_tax_amount_label }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class='color-subtext'>{{ __('order.promotion_discount_amount') }}</td>
                                        <td>
                                            <div>
                                                <span v-show='checking' class='spinner-grow spinner-grow-sm'
                                                      role='status' aria-hidden='true'></span>
                                                <span class='fw-bold' :class="{'text-success': child_promotion_amount}">{{
                                                        child_promotion_amount_label
                                                    }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <button type='button' v-ec-modal.add-discounts
                                                    class='btn btn text-primary p-0'>
                                                    <span v-if='!has_applied_discount'>
                                                        <i class='fa fa-plus-circle'></i> {{ __('order.add_discount') }}</span>
                                                <span v-else>{{ __('order.discount') }}</span>
                                            </button>
                                            <span class='d-block small fw-bold' v-if='has_applied_discount'>{{
                                                    child_coupon_code || child_discount_description
                                                }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <span v-show='checking' class='spinner-grow spinner-grow-sm'
                                                      role='status' aria-hidden='true'></span>
                                                <span :class="{'text-success fw-bold': child_discount_amount}">{{
                                                        child_discount_amount_label
                                                    }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if='is_available_shipping'>
                                        <td>
                                            <button type='button' v-ec-modal.add-shipping
                                                    class='btn btn text-primary p-0'>
                                                    <span v-if='!child_is_selected_shipping'>
                                                        <i class='fa fa-plus-circle'></i> {{
                                                            __('order.add_shipping_fee')
                                                        }}</span>
                                                <span v-else>{{ __('order.shipping') }}</span>
                                            </button>
                                            <span class='d-block small fw-bold' v-if='child_shipping_method_name'>{{
                                                    child_shipping_method_name
                                                }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <span v-show='checking' class='spinner-grow spinner-grow-sm'
                                                      role='status' aria-hidden='true'></span>
                                                <span :class="{'fw-bold': child_shipping_amount}">{{
                                                        child_shipping_amount_label
                                                    }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class='text-no-bold'>
                                        <td>{{ __('order.total_amount') }}</td>
                                        <td>
                                                <span>
                                                    <span v-show='checking' class='spinner-grow spinner-grow-sm'
                                                          role='status' aria-hidden='true'></span>
                                                    <span class='fs-5'>{{ child_total_amount_label }}</span>
                                                </span>
                                        </td>
                                    </tr>
                                    <tr class='text-no-bold'>
                                        <td colspan='2'>
                                            <div>{{ __('order.payment_method') }}</div>
                                            <div class='ui-select-wrapper'>
                                                <select class='ui-select' v-model='child_payment_method'>
                                                    <option value='cod'>{{ __('order.cash_on_delivery_cod') }}</option>
                                                    <option value='bank_transfer'>{{
                                                            __('order.bank_transfer')
                                                        }}
                                                    </option>
                                                </select>
                                                <svg class='svg-next-icon svg-next-icon-size-16'>
                                                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'>
                                                        <path d='M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z'></path>
                                                    </svg>
                                                </svg>
                                            </div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='pd-all-10-20 border-top-color'>
                    <div class='row'>
                        <div class='col-12 col-sm-6 col-md-12 col-lg-6'>
                            <div class='flexbox-grid-default mt5 mb5'>
                                <div class='flexbox-auto-left p-r10'>
                                    <i class='fa fa-credit-card fa-1-5 color-blue'></i>
                                </div>
                                <div class='flexbox-auto-content'>
                                    <div class='text-upper ws-nm'>{{
                                            __('order.confirm_payment_and_create_order')
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='col-12 col-sm-6 col-md-12 col-lg-6 text-end'>
                            <button class='btn btn-success' v-ec-modal.make-paid
                                    :disabled="!child_product_ids.length || child_payment_method == 'cod'">
                                {{ __('order.paid') }}
                            </button>
                            <button class='btn btn-primary ml15' v-ec-modal.make-pending
                                    :disabled='!child_product_ids.length || child_total_amount === 0'>
                                {{ __('order.pay_later') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='flexbox-content flexbox-right'>
            <div class='wrapper-content mb20'>
                <div v-if='!child_customer_id || !child_customer'>
                    <div class='next-card-section'>
                        <div class='flexbox-grid-default mb15'>
                            <div class='flexbox-auto-content'>
                                <label class='title-product-main'>{{ __('order.customer_information') }}</label>
                            </div>
                        </div>
                        <div class='findcustomer'>
                            <div class='box-search-advance customer'>
                                <div>
                                    <input type='text' class='next-input textbox-advancesearch customer'
                                           @click='loadListCustomersForSearch()'
                                           @keyup='handleSearchCustomer($event.target.value)'
                                           :placeholder="__('order.search_or_create_new_customer')">
                                </div>
                                <div class='panel panel-default'
                                     :class='{ active: customers, hidden : hidden_customer_search_panel }'>
                                    <div class='panel-body'>
                                        <div class='box-search-advance-head' v-ec-modal.add-customer>
                                            <div class='flexbox-grid-default flexbox-align-items-center'>
                                                <div class='flexbox-auto-40'>
                                                    <img width='30'
                                                         src='/vendor/core/plugins/ecommerce/images/next-create-customer.svg'
                                                         alt='icon'>
                                                </div>
                                                <div class='flexbox-auto-content-right'>
                                                    <span>{{ __('order.create_new_customer') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='list-search-data'>
                                            <div class='has-loading' v-show='loading'>
                                                <i class='fa fa-spinner fa-spin'></i>
                                            </div>
                                            <ul class='clearfix' v-show='!loading'>
                                                <li class='row' v-for='customer in customers.data'
                                                    v-bind:key='customer.id'
                                                    @click='selectCustomer(customer)'>
                                                    <div class='flexbox-grid-default flexbox-align-items-center'>
                                                        <div class='flexbox-auto-40'>
                                                            <div
                                                                class='wrap-img inline_block vertical-align-t radius-cycle'>
                                                                <img class='thumb-image radius-cycle'
                                                                     :src='customer.avatar_url' :alt='customer.name'>
                                                            </div>
                                                        </div>
                                                        <div class='flexbox-auto-content-right'>
                                                            <div class='overflow-ellipsis'>{{ customer.name }}</div>
                                                            <div class='overflow-ellipsis'>
                                                                <a :href="'mailto:' + customer.email">
                                                                    <span>{{ customer.email || '-' }}</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li v-if='customers.data && customers.data.length === 0'>
                                                    <span>{{ __('order.no_customer_found') }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class='panel-footer'
                                         v-if='customers.next_page_url || customers.prev_page_url'>
                                        <div class='btn-group float-end'>
                                            <button type='button'
                                                    @click='loadListCustomersForSearch((customers.prev_page_url ? customers.current_page - 1 : customers.current_page), true)'
                                                    :class="{ 'btn btn-secondary': customers.current_page !== 1, 'btn btn-secondary disable': customers.current_page === 1}"
                                                    :disabled='customers.current_page === 1'>
                                                <svg role='img'
                                                     class='svg-next-icon svg-next-icon-size-16 svg-next-icon-rotate-180'>
                                                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'>
                                                        <path d='M6 4l9 8-9 8 2 2 11-10L8 2 6 4'
                                                              fill='currentColor'></path>
                                                    </svg>
                                                </svg>
                                            </button>
                                            <button type='button'
                                                    @click='loadListCustomersForSearch((customers.next_page_url ? customers.current_page + 1 : customers.current_page), true)'
                                                    :class="{ 'btn btn-secondary': customers.next_page_url, 'btn btn-secondary disable': !customers.next_page_url }"
                                                    :disabled='!customers.next_page_url'>
                                                <svg role='img' class='svg-next-icon svg-next-icon-size-16'>
                                                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'>
                                                        <path d='M6 4l9 8-9 8 2 2 11-10L8 2 6 4'
                                                              fill='currentColor'></path>
                                                    </svg>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class='clearfix'></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if='child_customer_id && child_customer'>
                    <div class='next-card-section p-none-b'>
                        <div class='flexbox-grid-default'>
                            <div class='flexbox-auto-content-left'>
                                <label class='title-product-main'>{{ __('order.customer') }}</label>
                            </div>
                            <div class='flexbox-auto-left'>
                                <a href='#' data-bs-toggle='tooltip' data-placement='top' title='Delete customer'
                                   @click='removeCustomer()'>
                                    <svg class='svg-next-icon svg-next-icon-size-12'>
                                        <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'
                                             enable-background='new 0 0 24 24'>
                                            <path
                                                d='M19.5 22c-.2 0-.5-.1-.7-.3L12 14.9l-6.8 6.8c-.2.2-.4.3-.7.3-.2 0-.5-.1-.7-.3l-1.6-1.6c-.1-.2-.2-.4-.2-.6 0-.2.1-.5.3-.7L9.1 12 2.3 5.2C2.1 5 2 4.8 2 4.5c0-.2.1-.5.3-.7l1.6-1.6c.2-.1.4-.2.6-.2.3 0 .5.1.7.3L12 9.1l6.8-6.8c.2-.2.4-.3.7-.3.2 0 .5.1.7.3l1.6 1.6c.1.2.2.4.2.6 0 .2-.1.5-.3.7L14.9 12l6.8 6.8c.2.2.3.4.3.7 0 .2-.1.5-.3.7l-1.6 1.6c-.2.1-.4.2-.6.2z'></path>
                                        </svg>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class='next-card-section border-none-t'>
                        <ul class='ws-nm'>
                            <li>
                                <img v-if='child_customer.avatar_url' class='width-60-px radius-cycle'
                                     :alt='child_customer.name'
                                     :src='child_customer.avatar_url'>
                                <div class='pull-right color_darkblue mt20'>
                                    <i class='fas fa-inbox'></i>
                                    <span>
                                        {{ child_customer_order_numbers }}
                                    </span>
                                    {{ __('order.orders') }}
                                </div>
                            </li>
                            <li class='mt10'>
                                <a class='hover-underline text-capitalize' href='#'>{{ child_customer.name }}</a>
                            </li>
                            <li>
                                <div class='flexbox-grid-default'>
                                    <div class='flexbox-auto-content-left overflow-ellipsis'>
                                        <a :href="'mailto:' + child_customer.email">
                                            <span>{{ child_customer.email || '-' }}</span>
                                        </a>
                                    </div>
                                    <div class='flexbox-auto-left'>
                                        <a v-ec-modal.edit-email>
                                            <span data-placement='top' data-bs-toggle='tooltip'
                                                  data-bs-original-title='Edit email'>
                                                <svg class='svg-next-icon svg-next-icon-size-12'>
                                                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 55.25 55.25'><path
                                                        d='M52.618,2.631c-3.51-3.508-9.219-3.508-12.729,0L3.827,38.693C3.81,38.71,3.8,38.731,3.785,38.749  c-0.021,0.024-0.039,0.05-0.058,0.076c-0.053,0.074-0.094,0.153-0.125,0.239c-0.009,0.026-0.022,0.049-0.029,0.075  c-0.003,0.01-0.009,0.02-0.012,0.03l-3.535,14.85c-0.016,0.067-0.02,0.135-0.022,0.202C0.004,54.234,0,54.246,0,54.259  c0.001,0.114,0.026,0.225,0.065,0.332c0.009,0.025,0.019,0.047,0.03,0.071c0.049,0.107,0.11,0.21,0.196,0.296  c0.095,0.095,0.207,0.168,0.328,0.218c0.121,0.05,0.25,0.075,0.379,0.075c0.077,0,0.155-0.009,0.231-0.027l14.85-3.535  c0.027-0.006,0.051-0.021,0.077-0.03c0.034-0.011,0.066-0.024,0.099-0.039c0.072-0.033,0.139-0.074,0.201-0.123  c0.024-0.019,0.049-0.033,0.072-0.054c0.008-0.008,0.018-0.012,0.026-0.02l36.063-36.063C56.127,11.85,56.127,6.14,52.618,2.631z   M51.204,4.045c2.488,2.489,2.7,6.397,0.65,9.137l-9.787-9.787C44.808,1.345,48.716,1.557,51.204,4.045z M46.254,18.895l-9.9-9.9  l1.414-1.414l9.9,9.9L46.254,18.895z M4.961,50.288c-0.391-0.391-1.023-0.391-1.414,0L2.79,51.045l2.554-10.728l4.422-0.491  l-0.569,5.122c-0.004,0.038,0.01,0.073,0.01,0.11c0,0.038-0.014,0.072-0.01,0.11c0.004,0.033,0.021,0.06,0.028,0.092  c0.012,0.058,0.029,0.111,0.05,0.165c0.026,0.065,0.057,0.124,0.095,0.181c0.031,0.046,0.062,0.087,0.1,0.127  c0.048,0.051,0.1,0.094,0.157,0.134c0.045,0.031,0.088,0.06,0.138,0.084C9.831,45.982,9.9,46,9.972,46.017  c0.038,0.009,0.069,0.03,0.108,0.035c0.036,0.004,0.072,0.006,0.109,0.006c0,0,0.001,0,0.001,0c0,0,0.001,0,0.001,0h0.001  c0,0,0.001,0,0.001,0c0.036,0,0.073-0.002,0.109-0.006l5.122-0.569l-0.491,4.422L4.204,52.459l0.757-0.757  C5.351,51.312,5.351,50.679,4.961,50.288z M17.511,44.809L39.889,22.43c0.391-0.391,0.391-1.023,0-1.414s-1.023-0.391-1.414,0  L16.097,43.395l-4.773,0.53l0.53-4.773l22.38-22.378c0.391-0.391,0.391-1.023,0-1.414s-1.023-0.391-1.414,0L10.44,37.738  l-3.183,0.354L34.94,10.409l9.9,9.9L17.157,47.992L17.511,44.809z M49.082,16.067l-9.9-9.9l1.415-1.415l9.9,9.9L49.082,16.067z' /></svg>
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class='next-card-section'>
                        <ul class='ws-nm'>
                            <li class='clearfix'>
                                <div class='flexbox-grid-default'>
                                    <div class='flexbox-auto-content-left'>
                                        <label class='title-text-second'>{{ __('order.shipping_address') }}</label>
                                    </div>
                                    <div class='flexbox-auto-left'>
                                        <a v-ec-modal.edit-address>
                                            <span data-placement='top' title='Update address' data-bs-toggle='tooltip'>
                                                <svg class='svg-next-icon svg-next-icon-size-12'>
                                                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 55.25 55.25'><path
                                                        d='M52.618,2.631c-3.51-3.508-9.219-3.508-12.729,0L3.827,38.693C3.81,38.71,3.8,38.731,3.785,38.749  c-0.021,0.024-0.039,0.05-0.058,0.076c-0.053,0.074-0.094,0.153-0.125,0.239c-0.009,0.026-0.022,0.049-0.029,0.075  c-0.003,0.01-0.009,0.02-0.012,0.03l-3.535,14.85c-0.016,0.067-0.02,0.135-0.022,0.202C0.004,54.234,0,54.246,0,54.259  c0.001,0.114,0.026,0.225,0.065,0.332c0.009,0.025,0.019,0.047,0.03,0.071c0.049,0.107,0.11,0.21,0.196,0.296  c0.095,0.095,0.207,0.168,0.328,0.218c0.121,0.05,0.25,0.075,0.379,0.075c0.077,0,0.155-0.009,0.231-0.027l14.85-3.535  c0.027-0.006,0.051-0.021,0.077-0.03c0.034-0.011,0.066-0.024,0.099-0.039c0.072-0.033,0.139-0.074,0.201-0.123  c0.024-0.019,0.049-0.033,0.072-0.054c0.008-0.008,0.018-0.012,0.026-0.02l36.063-36.063C56.127,11.85,56.127,6.14,52.618,2.631z   M51.204,4.045c2.488,2.489,2.7,6.397,0.65,9.137l-9.787-9.787C44.808,1.345,48.716,1.557,51.204,4.045z M46.254,18.895l-9.9-9.9  l1.414-1.414l9.9,9.9L46.254,18.895z M4.961,50.288c-0.391-0.391-1.023-0.391-1.414,0L2.79,51.045l2.554-10.728l4.422-0.491  l-0.569,5.122c-0.004,0.038,0.01,0.073,0.01,0.11c0,0.038-0.014,0.072-0.01,0.11c0.004,0.033,0.021,0.06,0.028,0.092  c0.012,0.058,0.029,0.111,0.05,0.165c0.026,0.065,0.057,0.124,0.095,0.181c0.031,0.046,0.062,0.087,0.1,0.127  c0.048,0.051,0.1,0.094,0.157,0.134c0.045,0.031,0.088,0.06,0.138,0.084C9.831,45.982,9.9,46,9.972,46.017  c0.038,0.009,0.069,0.03,0.108,0.035c0.036,0.004,0.072,0.006,0.109,0.006c0,0,0.001,0,0.001,0c0,0,0.001,0,0.001,0h0.001  c0,0,0.001,0,0.001,0c0.036,0,0.073-0.002,0.109-0.006l5.122-0.569l-0.491,4.422L4.204,52.459l0.757-0.757  C5.351,51.312,5.351,50.679,4.961,50.288z M17.511,44.809L39.889,22.43c0.391-0.391,0.391-1.023,0-1.414s-1.023-0.391-1.414,0  L16.097,43.395l-4.773,0.53l0.53-4.773l22.38-22.378c0.391-0.391,0.391-1.023,0-1.414s-1.023-0.391-1.414,0L10.44,37.738  l-3.183,0.354L34.94,10.409l9.9,9.9L17.157,47.992L17.511,44.809z M49.082,16.067l-9.9-9.9l1.415-1.415l9.9,9.9L49.082,16.067z' /></svg>
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </li>
                            <li class='text-infor-subdued mt15'>
                                <div v-if='child_customer_addresses.length > 1'>
                                    <div class='ui-select-wrapper'>
                                        <select class='ui-select' @change='selectCustomerAddress($event)'>
                                            <option v-for='address_item in child_customer_addresses'
                                                    :value='address_item.id'
                                                    :selected='parseInt(address_item.id) === parseInt(customer_address.email)'
                                                    v-bind:key='address_item.id'>
                                                {{ address_item.full_address }}
                                            </option>
                                        </select>
                                        <svg class='svg-next-icon svg-next-icon-size-16'>
                                            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'>
                                                <path d='M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z'></path>
                                            </svg>
                                        </svg>
                                    </div>
                                    <br>
                                </div>
                                <div>{{ child_customer_address.name }}</div>
                                <div>{{ child_customer_address.phone }}</div>
                                <div>
                                    <a :href="'mailto:' + child_customer_address.email">{{
                                            child_customer_address.email
                                        }}</a>
                                </div>
                                <div>{{ child_customer_address.address }}</div>
                                <div>{{ child_customer_address.city_name }}</div>
                                <div>{{ child_customer_address.state_name }}</div>
                                <div>{{ child_customer_address.country_name }}</div>
                                <div v-if='zip_code_enabled'>{{ child_customer_address.zip_code }}</div>
                                <div v-if='child_customer_address.full_address'>
                                    <a target='_blank' class='hover-underline'
                                       :href="'https://maps.google.com/?q=' + child_customer_address.full_address">{{
                                            __('order.see_on_maps')
                                        }}</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <!--/ko-->
                </div>
            </div>
        </div>

        <AddProductModal @create-product='createProduct' :store='store'></AddProductModal>

        <ec-modal id='add-discounts' title='Add discount' :ok-title="__('order.add_discount')"
                 :cancel-title="__('order.close')"
                 @ok='handleAddDiscount($event)'>
            <div class='next-form-section'>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.discount_based_on') }}</label>
                        <div class='flexbox-grid-default'>
                            <div class='flexbox-auto-left'>
                                <div class='flexbox-input-group'>
                                    <button value='amount' class='item-group btn btn-secondary btn-active'
                                            :class="{ active : discount_type === 'amount' }"
                                            @click='changeDiscountType($event)'>
                                        {{ currency || '$' }}
                                    </button>&nbsp;
                                    <button value='percentage'
                                            class='item-group border-radius-right-none btn btn-secondary btn-active'
                                            :class="{ active : discount_type === 'percentage' }"
                                            @click='changeDiscountType($event)'>
                                        %
                                    </button>&nbsp;
                                </div>
                            </div>
                            <div class='flexbox-auto-content'>
                                <div class='next-input--stylized border-radius-left-none'>
                                    <input class='next-input next-input--invisible' v-model='discount_custom_value'>
                                    <span class='next-input-add-on next-input__add-on--after'>{{
                                            discount_type_unit
                                        }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.or_coupon_code') }}</label>
                        <div class='next-input--stylized' :class="{ 'field-has-error' : has_invalid_coupon }">
                            <input class='next-input next-input--invisible coupon-code-input'
                                   :value='child_coupon_code'>
                        </div>
                    </div>
                </div>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.description') }}</label>
                        <input :placeholder="__('order.discount_description')" class='next-input'
                               v-model='child_discount_description'>
                    </div>
                </div>
            </div>
        </ec-modal>

        <ec-modal id='add-shipping' :title="__('order.shipping_fee')" :ok-title="__('order.update')"
                 :cancel-title="__('order.close')"
                 @ok='selectShippingMethod($event)'>
            <div class='next-form-section'>
                <div class='ui-layout__item mb15 p-none-important'
                     v-if='!child_products.length || !child_customer_address.phone'>
                    <div class='ui-banner ui-banner--status-info'>
                        <div class='ui-banner__ribbon'>
                            <svg class='svg-next-icon svg-next-icon-size-20'>
                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'><title>Circle-Alert</title>
                                    <path fill='currentColor'
                                          d='M19 10c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z'></path>
                                    <path
                                        d='M10 0C4.486 0 0 4.486 0 10s4.486 10 10 10 10-4.486 10-10S15.514 0 10 0zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-13c-.552 0-1 .447-1 1v4c0 .553.448 1 1 1s1-.447 1-1V6c0-.553-.448-1-1-1zm0 8c-.552 0-1 .447-1 1s.448 1 1 1 1-.447 1-1-.448-1-1-1z'></path>
                                </svg>
                            </svg>
                        </div>
                        <div class='ui-banner__content'>
                            <h2 class='ui-banner__title'>{{ __('order.how_to_select_configured_shipping') }}</h2>
                            <div class='ws-nm'>
                                <p>{{ __('order.please_products_and_customer_address_to_see_the_shipping_rates') }}.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='next-label'>
                            <input type='radio' class='hrv-radio' value='free-shipping' name='shipping_type'
                                   v-model='shipping_type'>
                            {{ __('order.free_shipping') }}
                        </label>
                    </div>
                </div>
                <div v-if='child_products.length && child_customer_address.phone'>
                    <div class='next-form-grid'>
                        <div class='next-form-grid-cell'>
                            <label class='next-label'>
                                <input type='radio' class='hrv-radio' value='custom' name='shipping_type'
                                       v-model='shipping_type'
                                       :disabled='shipping_methods && ! Object.keys(shipping_methods).length'>
                                <span>{{ __('order.custom') }}</span>
                                <span class='small text-warning'
                                      v-if='shipping_methods && ! Object.keys(shipping_methods).length'>{{
                                        __('order.shipping_method_not_found')
                                    }}</span>
                            </label>
                        </div>
                    </div>
                    <div class='next-form-grid' v-show="shipping_type == 'custom'">
                        <div class='next-form-grid-cell'>
                            <div class='ui-select-wrapper'>
                                <select class='ui-select'>
                                    <option
                                        v-for='(shipping, shipping_key) in shipping_methods'
                                        :value='shipping_key'
                                        :selected="shipping_key === (child_shipping_method + ';' + child_shipping_option)"
                                        v-bind:key='shipping_key'
                                        :data-shipping-method='shipping.method'
                                        :data-shipping-option='shipping.option'>
                                        {{ shipping.title }}
                                    </option>
                                </select>
                                <svg class='svg-next-icon svg-next-icon-size-16'>
                                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'>
                                        <path d='M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z'></path>
                                    </svg>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ec-modal>

        <ec-modal id='make-paid' :title="__('order.confirm_payment_is_paid_for_this_order')"
                 :ok-title="__('order.create_order')" :cancel-title="__('order.close')"
                 @ok='createOrder($event, true)'>
            <div class='note note-warning'>
                {{
                    __('order.payment_status_of_the_order_is_paid_once_the_order_has_been_created_you_cannot_change_the_payment_method_or_status')
                }}.
            </div>
            <br />
            <p>
                <span>{{ __('order.paid_amount') }}:</span>
                <span class='fs-5'>{{ child_total_amount_label }}</span>
            </p>
        </ec-modal>

        <ec-modal id='make-pending' :title="__('order.confirm_that_payment_for_this_order_will_be_paid_later')"
                 :ok-title="__('order.create_order')"
                 :cancel-title="__('order.close')" @ok='createOrder($event)'>
            <div class='note note-warning'>
                {{
                    __('order.payment_status_of_the_order_is_pending_once_the_order_has_been_created_you_cannot_change_the_payment_method_or_status')
                }}.
            </div>
            <br />
            <p>
                <span class="me-1">{{ __('order.pending_amount') }}:</span>
                <span class='fs-5'>{{ child_total_amount_label }}</span>
            </p>
        </ec-modal>

        <OrderCustomerAddress
            :child_customer_address='child_customer_address'
            :zip_code_enabled='zip_code_enabled'
            :use_location_data='use_location_data'
            @update-order-address='updateOrderAddress'
            @update-customer-email='updateCustomerEmail'
            @create-new-customer='createNewCustomer'></OrderCustomerAddress>
    </div>
</template>

<script>
import ProductAction from './partials/ProductActionComponent.vue'
import OrderCustomerAddress from './partials/OrderCustomerAddressComponent.vue'
import AddProductModal from './partials/AddProductModalComponent.vue'

export default {
    props: {
        products: {
            type: Array,
            default: () => [],
        },
        product_ids: {
            type: Array,
            default: () => [],
        },
        customer_id: {
            type: Number,
            default: () => null,
        },
        customer: {
            type: Object,
            default: () => ({
                email: 'guest@example.com',
            }),
        },
        customer_addresses: {
            type: Array,
            default: () => [],
        },
        customer_address: {
            type: Object,
            default: () => ({
                name: null,
                email: null,
                address: null,
                phone: null,
                country: null,
                state: null,
                city: null,
                zip_code: null,
            }),
        },
        customer_order_numbers: {
            type: Number,
            default: () => 0,
        },
        sub_amount: {
            type: Number,
            default: () => 0,
        },
        sub_amount_label: {
            type: String,
            default: () => '',
        },
        tax_amount: {
            type: Number,
            default: () => 0,
        },
        tax_amount_label: {
            type: String,
            default: () => '',
        },
        total_amount: {
            type: Number,
            default: () => 0,
        },
        total_amount_label: {
            type: String,
            default: () => '',
        },
        coupon_code: {
            type: String,
            default: () => '',
        },
        promotion_amount: {
            type: Number,
            default: () => 0,
        },
        promotion_amount_label: {
            type: String,
            default: () => '',
        },
        discount_amount: {
            type: Number,
            default: () => 0,
        },
        discount_amount_label: {
            type: String,
            default: () => '',
        },
        discount_description: {
            type: String,
            default: () => null,
        },
        shipping_amount: {
            type: Number,
            default: () => 0,
        },
        shipping_amount_label: {
            type: String,
            default: () => '',
        },
        shipping_method: {
            type: String,
            default: () => 'default',
        },
        shipping_option: {
            type: String,
            default: () => '',
        },
        is_selected_shipping: {
            type: Boolean,
            default: () => false,
        },
        shipping_method_name: {
            type: String,
            default: function() {
                return ('order.free_shipping')
            },
        },
        payment_method: {
            type: String,
            default: () => 'cod',
        },
        currency: {
            type: String,
            default: () => null,
            required: true,
        },
        zip_code_enabled: {
            type: Number,
            default: () => 0,
            required: true,
        },
        use_location_data: {
            type: Number,
            default: () => 0,
        },
        is_tax_enabled: {
            type: Number,
            default: () => true,
        },
    },
    data: function() {
        return {
            list_products: {
                data: [],
            },
            hidden_product_search_panel: true,
            loading: false,
            checking: false,
            note: null,
            customers: {
                data: [],
            },
            hidden_customer_search_panel: true,
            customer_keyword: null,
            shipping_type: 'free-shipping',
            shipping_methods: {},
            discount_type_unit: this.currency,
            discount_type: 'amount',
            child_discount_description: this.discount_description,
            has_invalid_coupon: false,
            has_applied_discount: this.discount_amount > 0,
            discount_custom_value: 0,
            child_coupon_code: this.coupon_code,
            child_customer: this.customer,
            child_customer_id: this.customer_id,
            child_customer_order_numbers: this.customer_order_numbers,
            child_customer_addresses: this.customer_addresses,
            child_customer_address: this.customer_address,
            child_products: this.products,
            child_product_ids: this.product_ids,
            child_sub_amount: this.sub_amount,
            child_sub_amount_label: this.sub_amount_label,
            child_tax_amount: this.tax_amount,
            child_tax_amount_label: this.tax_amount_label,
            child_total_amount: this.total_amount,
            child_total_amount_label: this.total_amount_label,
            child_promotion_amount: this.promotion_amount,
            child_promotion_amount_label: this.promotion_amount_label,
            child_discount_amount: this.discount_amount,
            child_discount_amount_label: this.discount_amount_label,
            child_shipping_amount: this.shipping_amount,
            child_shipping_amount_label: this.shipping_amount_label,
            child_shipping_method: this.shipping_method,
            child_shipping_option: this.shipping_option,
            child_shipping_method_name: this.shipping_method_name,
            child_is_selected_shipping: this.is_selected_shipping,
            child_payment_method: this.payment_method,
            productSearchRequest: null,
            timeoutProductRequest: null,
            customerSearchRequest: null,
            checkDataOrderRequest: null,
            store: {
                id: 0,
                name: null,
            },
            is_available_shipping: false,
        }
    },
    components: {
        ProductAction,
        OrderCustomerAddress,
        AddProductModal,
    },
    mounted: function() {
        let context = this
        $(document).on('click', 'body', e => {
            let container = $('.box-search-advance')

            if (!container.is(e.target) && container.has(e.target).length === 0) {
                context.hidden_customer_search_panel = true
                context.hidden_product_search_panel = true
            }
        })

        if (context.product_ids) {
            context.checkDataBeforeCreateOrder()
        }
    },
    methods: {
        loadListCustomersForSearch: function(page = 1, force = false) {
            let context = this
            context.hidden_customer_search_panel = false
            $('.textbox-advancesearch.customer').closest('.box-search-advance.customer').find('.panel').addClass('active')
            if (_.isEmpty(context.customers.data) || force) {
                context.loading = true
                if (context.customerSearchRequest) {
                    context.customerSearchRequest.abort()
                }

                context.customerSearchRequest = new AbortController()

                axios
                    .get(route('customers.get-list-customers-for-search', {
                        keyword: context.customer_keyword,
                        page: page,
                    }), { signal: context.customerSearchRequest.signal })
                    .then(res => {
                        context.customers = res.data.data
                        context.loading = false
                    })
                    .catch(error => {
                        if (!axios.isCancel(error)) {
                            context.loading = false
                            Botble.handleError(error.response.data)
                        }
                    })
            }
        },
        handleSearchCustomer: function(value) {
            if (value !== this.customer_keyword) {
                let context = this
                this.customer_keyword = value
                setTimeout(() => {
                    context.loadListCustomersForSearch(1, true)
                }, 500)
            }
        },
        loadListProductsAndVariations: function(page = 1, force = false, show_panel = true) {
            let context = this
            if (show_panel) {
                context.hidden_product_search_panel = false
                $('.textbox-advancesearch.product').closest('.box-search-advance.product').find('.panel').addClass('active')
            } else {
                context.hidden_product_search_panel = true
            }

            if (_.isEmpty(context.list_products.data) || force) {
                context.loading = true
                if (context.productSearchRequest) {
                    context.productSearchRequest.abort()
                }

                context.productSearchRequest = new AbortController()

                axios
                    .get(route('products.get-all-products-and-variations', {
                        keyword: context.product_keyword,
                        page: page,
                        product_ids: context.child_product_ids,
                    }), { signal: context.productSearchRequest.signal })
                    .then(res => {
                        context.list_products = res.data.data
                        context.loading = false
                    })
                    .catch(error => {
                        if (!axios.isCancel(error)) {
                            Botble.handleError(error.response.data)
                            context.loading = false
                        }
                    })
            }
        },
        handleSearchProduct: function(value) {
            if (value !== this.product_keyword) {
                let context = this
                context.product_keyword = value
                if (context.timeoutProductRequest) {
                    clearTimeout(context.timeoutProductRequest)
                }

                context.timeoutProductRequest = setTimeout(() => {
                    context.loadListProductsAndVariations(1, true)
                }, 1000)
            }
        },
        selectProductVariant: function(product, refOptions) {
            let context = this
            if (_.isEmpty(product) && product.is_out_of_stock) {
                Botble.showError(context.__('order.cant_select_out_of_stock_product'))
                return false
            }
            const requiredOptions = product.product_options.filter((item) => item.required)

            if (product.is_variation || !product.variations.length) {
                let refAction = context.$refs['product_actions_' + product.original_product_id][0]
                refOptions = refAction.$refs['product_options_' + product.original_product_id]
            }

            let productOptions = refOptions.values

            if (requiredOptions.length) {
                let errorMessage = []
                requiredOptions.forEach(item => {
                    if (!productOptions[item.id]) {
                        errorMessage.push(context.__('order.please_choose_product_option') + ': ' + item.name)
                    }
                })

                if (errorMessage && errorMessage.length) {
                    errorMessage.forEach(message => {
                        Botble.showError(message)
                    })
                    return
                }
            }

            let options = []

            product.product_options.map((item) => {
                options[item.id] = {
                    option_type: item.option_type,
                    values: productOptions[item.id],
                }
            })
            context.child_products.push({ id: product.id, quantity: 1, options })
            context.checkDataBeforeCreateOrder()

            context.hidden_product_search_panel = true
        },
        selectCustomer: function(customer) {
            this.child_customer = customer
            this.child_customer_id = customer.id

            this.loadCustomerAddress(this.child_customer_id)

            this.getOrderNumbers()
        },
        checkDataBeforeCreateOrder: function(data = {}, onSuccess = null, onError = null) {
            let context = this
            let formData = { ...context.getOrderFormData(), ...data }

            context.checking = true
            if (context.checkDataOrderRequest) {
                context.checkDataOrderRequest.abort()
            }

            context.checkDataOrderRequest = new AbortController()

            axios
                .post(route('orders.check-data-before-create-order'), formData, { signal: context.checkDataOrderRequest.signal })
                .then(res => {
                    let data = res.data.data

                    if (data.update_context_data) {
                        context.child_products = data.products
                        context.child_product_ids = _.map(data.products, 'id')

                        context.child_sub_amount = data.sub_amount
                        context.child_sub_amount_label = data.sub_amount_label

                        context.child_tax_amount = data.tax_amount
                        context.child_tax_amount_label = data.tax_amount_label

                        context.child_promotion_amount = data.promotion_amount
                        context.child_promotion_amount_label = data.promotion_amount_label

                        context.child_discount_amount = data.discount_amount
                        context.child_discount_amount_label = data.discount_amount_label

                        context.child_shipping_amount = data.shipping_amount
                        context.child_shipping_amount_label = data.shipping_amount_label

                        context.child_total_amount = data.total_amount
                        context.child_total_amount_label = data.total_amount_label

                        context.shipping_methods = data.shipping_methods

                        context.child_shipping_method_name = data.shipping_method_name
                        context.child_shipping_method = data.shipping_method
                        context.child_shipping_option = data.shipping_option
                        context.is_available_shipping = data.is_available_shipping

                        context.store = data.store && data.store.id ? data.store : { id: 0, name: null }
                    }

                    if (res.data.error) {
                        Botble.showError(res.data.message)
                        if (onError) {
                            onError()
                        }
                    } else {
                        if (onSuccess) {
                            onSuccess()
                        }
                    }
                    context.checking = false
                })
                .catch(error => {
                    if (!axios.isCancel(error)) {
                        context.checking = false
                        Botble.handleError(error.response.data)
                    }
                })
        },
        getOrderFormData: function() {
            let products = []
            _.each(this.child_products, function(item) {
                products.push({
                    id: item.id,
                    quantity: item.select_qty,
                    options: item.options,
                })
            })

            return {
                products,
                payment_method: this.child_payment_method,
                shipping_method: this.child_shipping_method,
                shipping_option: this.child_shipping_option,
                shipping_amount: this.child_shipping_amount,
                discount_amount: this.child_discount_amount,
                discount_description: this.child_discount_description,
                coupon_code: this.child_coupon_code,
                customer_id: this.child_customer_id,
                note: this.note,
                sub_amount: this.child_sub_amount,
                tax_amount: this.child_tax_amount,
                amount: this.child_total_amount,
                customer_address: this.child_customer_address,
                discount_type: this.discount_type,
                discount_custom_value: this.discount_custom_value,
                shipping_type: this.shipping_type,
            }
        },
        removeCustomer: function() {
            this.child_customer = this.customer
            this.child_customer_id = null
            this.child_customer_addresses = []
            this.child_customer_address = {
                name: null,
                email: null,
                address: null,
                phone: null,
                country: null,
                state: null,
                city: null,
                zip_code: null,
                full_address: null,
            }
            this.child_customer_order_numbers = 0

            this.checkDataBeforeCreateOrder()
        },
        handleRemoveVariant: function(event, variant, vKey) {
            event.preventDefault()
            this.child_product_ids = this.child_product_ids.filter((item, k) => k !== vKey)
            this.child_products = this.child_products.filter((item, k) => k !== vKey)

            this.checkDataBeforeCreateOrder()
        },
        createOrder: function(event, paid = false) {
            event.preventDefault()
            $(event.target).addClass('button-loading')

            let formData = this.getOrderFormData()
            formData.payment_status = paid ? 'completed' : 'pending'

            axios
                .post(route('orders.create'), formData)
                .then(res => {
                    let data = res.data.data
                    if (res.data.error) {
                        Botble.showError(res.data.message)
                    } else {
                        Botble.showSuccess(res.data.message)
                        if (paid) {
                            $event.emit('ec-modal:close', 'make-paid')
                        } else {
                            $event.emit('ec-modal:close', 'make-pending')
                        }

                        setTimeout(() => {
                            window.location.href = route('orders.edit', data.id)
                        }, 1000)
                    }
                })
                .catch(res => {
                    Botble.handleError(res.response.data)
                })
                .then(() => {
                    $(event.target).removeClass('button-loading')
                })
        },
        createProduct: function(event, product) {
            event.preventDefault()
            $(event.target).addClass('button-loading')
            let context = this
            if (context.store && context.store.id) {
                product.store_id = context.store.id
            }

            axios
                .post(route('products.create-product-when-creating-order'), product)
                .then(res => {
                    if (res.data.error) {
                        Botble.showError(res.data.message)
                    } else {

                        context.product = res.data.data

                        context.list_products = {
                            data: [],
                        }

                        let productItem = context.product
                        productItem.select_qty = 1

                        context.child_products.push(productItem)
                        context.child_product_ids.push(context.product.id)

                        context.hidden_product_search_panel = true

                        Botble.showSuccess(res.data.message)

                        $event.emit('ec-modal:close', 'add-product-item')

                        context.checkDataBeforeCreateOrder()
                    }
                })
                .catch(res => {
                    Botble.handleError(res.response.data)
                })
                .then(() => {
                    $(event.target).removeClass('button-loading')
                })
        },
        updateCustomerEmail: function(event) {
            event.preventDefault()
            $(event.target).addClass('button-loading')

            let context = this

            axios
                .post(route('customers.update-email', context.child_customer_address.id), {
                    email: context.child_customer_address.email,
                })
                .then(res => {
                    if (res.data.error) {
                        Botble.showError(res.data.message)
                    } else {
                        Botble.showSuccess(res.data.message)

                        $event.emit('ec-modal:close', 'edit-email')
                    }
                })
                .catch(res => {
                    Botble.handleError(res.response.data)
                })
                .then(() => {
                    $(event.target).removeClass('button-loading')
                })
        },
        updateOrderAddress: function(event) {
            event.preventDefault()
            if (this.customer) {
                $(event.target).addClass('button-loading')

                this.checkDataBeforeCreateOrder({}, () => {
                    setTimeout(() => {
                        $(event.target).removeClass('button-loading')
                        $event.emit('ec-modal:close', 'edit-address')
                    }, 500)
                }, () => {
                    setTimeout(() => {
                        $(event.target).removeClass('button-loading')
                    }, 500)
                })
            }
        },
        createNewCustomer: function(event) {
            event.preventDefault()
            let context = this

            $(event.target).addClass('button-loading')

            axios
                .post(route('customers.create-customer-when-creating-order'), {
                    customer_id: context.child_customer_id,
                    name: context.child_customer_address.name,
                    email: context.child_customer_address.email,
                    phone: context.child_customer_address.phone,
                    address: context.child_customer_address.address,
                    country: context.child_customer_address.country,
                    state: context.child_customer_address.state,
                    city: context.child_customer_address.city,
                    zip_code: context.child_customer_address.zip_code,
                })
                .then(res => {
                    if (res.data.error) {
                        Botble.showError(res.data.message)
                    } else {
                        context.child_customer_address = res.data.data.address
                        context.child_customer = res.data.data.customer
                        context.child_customer_id = context.child_customer.id

                        context.customers = {
                            data: [],
                        }

                        Botble.showSuccess(res.data.message)
                        context.checkDataBeforeCreateOrder()

                        $event.emit('ec-modal:close', 'add-customer')
                    }
                })
                .catch(res => {
                    Botble.handleError(res.response.data)
                })
                .then(() => {
                    $(event.target).removeClass('button-loading')
                })
        },
        selectCustomerAddress: function(event) {
            let context = this
            _.each(this.child_customer_addresses, (item) => {
                if (parseInt(item.id) === parseInt(event.target.value)) {
                    context.child_customer_address = item
                }
            })

            this.checkDataBeforeCreateOrder()
        },
        getOrderNumbers: function() {
            let context = this
            axios
                .get(route('customers.get-customer-order-numbers', context.child_customer_id))
                .then(res => {
                    context.child_customer_order_numbers = res.data.data
                })
                .catch(res => {
                    Botble.handleError(res.response.data)
                })
        },
        loadCustomerAddress: function() {
            let context = this
            axios
                .get(route('customers.get-customer-addresses', context.child_customer_id))
                .then(res => {
                    context.child_customer_addresses = res.data.data
                    if (!_.isEmpty(context.child_customer_addresses)) {
                        context.child_customer_address = _.first(context.child_customer_addresses)
                    }
                    this.checkDataBeforeCreateOrder()
                })
                .catch(res => {
                    Botble.handleError(res.response.data)
                })
        },
        selectShippingMethod: function(event) {
            event.preventDefault()
            let context = this
            let $button = $(event.target).find('.btn-primary')
            $button.addClass('button-loading')

            context.child_is_selected_shipping = true

            if (context.shipping_type === 'free-shipping') {
                context.child_shipping_method_name = context.__('order.free_shipping')
                context.child_shipping_amount = 0
            } else {
                let selected_shipping = $(event.target).find('.ui-select').val()
                if (!_.isEmpty(selected_shipping)) {
                    let option = $(event.target).find('.ui-select option:selected')
                    context.child_shipping_method = option.data('shipping-method')
                    context.child_shipping_option = option.data('shipping-option')
                }
            }

            this.checkDataBeforeCreateOrder({}, () => {
                setTimeout(function() {
                    $button.removeClass('button-loading')
                    $event.emit('ec-modal:close', 'add-shipping')
                }, 500)
            }, () => {
                setTimeout(function() {
                    $button.removeClass('button-loading')
                }, 500)
            })
        },
        changeDiscountType: function(event) {
            if ($(event.target).val() === 'amount') {
                this.discount_type_unit = this.currency
            } else {
                this.discount_type_unit = '%'
            }
            this.discount_type = $(event.target).val()
        },
        handleAddDiscount: function(event) {
            event.preventDefault()
            let $target = $(event.target)
            let context = this

            context.has_applied_discount = true
            context.has_invalid_coupon = false

            let $button = $target.find('.btn-primary')

            $button.addClass('button-loading').prop('disabled', true)
            context.child_coupon_code = $target.find('.coupon-code-input').val()

            if (context.child_coupon_code) {
                context.discount_custom_value = 0
            } else {
                context.discount_custom_value = Math.max(parseFloat(context.discount_custom_value), 0)
                if (context.discount_type === 'percentage') {
                    context.discount_custom_value = Math.min(context.discount_custom_value, 100)
                }
            }

            context.checkDataBeforeCreateOrder({}, () => {
                setTimeout(function() {
                    if (!context.child_coupon_code && !context.discount_custom_value) {
                        context.has_applied_discount = false
                    }
                    $button.removeClass('button-loading').prop('disabled', false)
                    $event.emit('ec-modal:close', 'add-discounts')
                }, 500)
            }, () => {
                if (context.child_coupon_code) {
                    context.has_invalid_coupon = true
                }
                $button.removeClass('button-loading').prop('disabled', false)
            })
        },
        handleChangeQuantity: function(event, variant, vKey) {
            event.preventDefault()
            let context = this
            variant.select_qty = parseInt(event.target.value)

            _.each(context.child_products, function(item, key) {
                if (vKey === key) {
                    if (variant.with_storehouse_management && parseInt(variant.select_qty) > variant.quantity) {
                        variant.select_qty = variant.quantity
                    }
                    context.child_products[key] = variant
                }
            })

            if (context.timeoutChangeQuantity) {
                clearTimeout(context.timeoutChangeQuantity)
            }

            context.timeoutChangeQuantity = setTimeout(() => {
                context.checkDataBeforeCreateOrder()
            }, 1500)
        },
    },
    watch: {
        child_payment_method: function() {
            this.checkDataBeforeCreateOrder()
        },
    },
}
</script>
