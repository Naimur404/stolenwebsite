<template>
    <div class="flexbox-grid no-pd-none">
        <div class="flexbox-content">
            <div class="wrapper-content">
                <div class="pd-all-20 ws-nm">
                    <label class="title-product-main text-no-bold">
                        <span>{{ __('discount.create_coupon_code')}}</span>
                    </label>
                    <a href="#" v-if="generateUrl" class="btn-change-link float-end" v-on:click="generateCouponCode($event)">{{ __('discount.generate_coupon_code')}}</a>
                    <div class="form-group mt15">
                        <input type="text" class="next-input coupon-code-input"
                            name="code" v-model="code">
                        <p class="help-block">{{ __('discount.customers_will_enter_this_coupon_code_when_they_checkout') }}.</p>
                    </div>
                    <div class="form-group mb0">
                        <input type="text" class="next-input" name="title" v-model="title"
                               :placeholder="__('discount.enter_coupon_name')">
                    </div>
                </div>
                <div class="pd-all-20 border-top-color">
                    <div class="form-group mb0 mt15">
                        <label>
                            <input type="checkbox" class="hrv-checkbox" name="is_unlimited" value="1"
                                   v-model="is_unlimited">{{ __('discount.unlimited_coupon')}}
                        </label>
                    </div>
                    <div class="form-group mb0 mt15" v-show="!is_unlimited">
                        <label class="text-title-field">{{ __('discount.enter_number') }}</label>
                        <div class="limit-input-group">
                            <input type="text" class="form-control pl5 p-r5" name="quantity" v-model="quantity"
                                   autocomplete="off" :disabled="is_unlimited">
                        </div>
                    </div>
                </div>
                <div class="pd-all-20 border-top-color">
                    <label class="title-product-main text-no-bold block-display">{{ __('discount.coupon_type') }}</label>
                    <div class="form-inline form-group discount-input mt15 mb0 ws-nm">
                        <div class="ui-select-wrapper inline_block mb5" style="min-width: 200px;">
                            <select id="discount-type-option" name="type_option" class="ui-select" v-model="type_option"
                                    @change="handleChangeTypeOption()">
                                <option v-for="(item, index) in type_options"
                                        :value="index"
                                        :key="index">{{ item }}
                                </option>
                            </select>
                            <svg class="svg-next-icon svg-next-icon-size-16">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z"></path></svg>
                            </svg>
                        </div>
                        <span class="lb-dis"> <span>{{ value_label }}</span></span>
                        <div class="inline mb5">
                            <div class="next-input--stylized">
                                <input type="text" class="next-input next-input--invisible" name="value"
                                       v-model="value" autocomplete="off" placeholder="0">
                                <span class="next-input-add-on next-input__add-on--after">{{ discountUnit }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flexbox-content flexbox-right">
            <div class="wrapper-content">
                <div class="pd-all-20">
                    <label class="title-product-main text-no-bold">{{ __('discount.time') }}</label>
                </div>
                <div class="pd-all-10-20 form-group mb0">
                    <label class="text-title-field">{{ __('discount.start_date')}}</label>
                    <div class="next-field__connected-wrapper z-index-9">
                        <div class="input-group me-2">
                            <div class="input-group datepicker">
                                <input type="text" :placeholder="dateFormat" :data-date-format="dateFormat" name="start_date" v-model="start_date"
                                       class="next-field--connected next-input"
                                       readonly data-input style="min-width: 0">
                                <a class="input-button" title="toggle" data-toggle><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 17 17"><g></g><path d="M14 2V1h-3v1H6V1H3v1H0v15h17V2h-3zM12 2h1v2h-1V2zM4 2h1v2H4V2zM16 16H1v-8.921h15V16zM1 6.079v-3.079h2v2h3V3h5v2h3V3h2v3.079H1z"></path></svg></a>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" placeholder="hh:mm" name="start_time"
                                   class="next-field--connected next-input z-index-9 time-picker timepicker timepicker-24" v-model="start_time" style="min-width: 0">
                            <span class="input-group-prepend">
                                <button class="btn default" type="button">
                                    <i class="fa fa-clock"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="pd-all-10-20 form-group mb0">
                    <label class="text-title-field">{{ __('discount.end_date')}}</label>
                    <div class="next-field__connected-wrapper z-index-9">
                        <div class="input-group me-2">
                            <div class="input-group datepicker">
                                <input type="text" :placeholder="dateFormat" :data-date-format="dateFormat" name="end_date" v-model="end_date"
                                       class="next-field--connected next-input"
                                       :disabled="unlimited_time" readonly data-input style="min-width: 0">
                                <a class="input-button" title="toggle" data-toggle><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 17 17"><g></g><path d="M14 2V1h-3v1H6V1H3v1H0v15h17V2h-3zM12 2h1v2h-1V2zM4 2h1v2H4V2zM16 16H1v-8.921h15V16zM1 6.079v-3.079h2v2h3V3h5v2h3V3h2v3.079H1z"></path></svg></a>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" placeholder="hh:mm" name="end_time"
                                   class="next-field--connected next-input z-index-9 time-picker timepicker timepicker-24" v-model="end_time"
                                   :disabled="unlimited_time" style="min-width: 0">
                            <span class="input-group-prepend">
                                <button class="btn default" type="button">
                                    <i class="fa fa-clock"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="pd-all-10-20">
                    <label class="next-label disable-input-date-discount">
                        <input type="checkbox" class="hrv-checkbox" name="unlimited_time" value="1"
                               v-model="unlimited_time">{{ __('discount.never_expired')}}
                    </label>
                </div>
            </div>

            <br>
            <div class="wrapper-content">
                <div class="pd-all-20">
                    <a class="btn btn-secondary me-2" :href="cancelUrl" v-if="cancelUrl">{{ __('discount.cancel') }}</a>
                    <button class="btn btn-primary">{{ __('discount.save') }}</button>
                </div>
            </div>
        </div>
    </div>

</template>

<style>
.date-time-group .invalid-feedback {
    display: none !important;
}
</style>

<script>
    let moment = require('moment');

    export default {
        data: () => {
            return {
                title: null,
                code: null,
                type: 'coupon',
                is_unlimited: true,
                quantity: 0,
                unlimited_time: true,
                start_date: moment().format('Y-MM-DD'),
                start_time: '00:00',
                end_date: moment().format('Y-MM-DD'),
                end_time: '23:59',
                type_option: 'amount',
                value: null,
                target: 'all-orders',
                can_use_with_promotion: false,
                value_label: '',
                hidden_product_search_panel: true,
                product_collection_id: null,
                product_collections: [],
                discount_on: 'per-order',
                min_order_price: null,
                loading: false,
                discountUnit: '$',
                type_options: []
            }
        },
        props: {
            currency: {
                type: String,
                default: () => null,
                required: true
            },
            generateUrl: {
                type: String,
                default: () => null,
            },
            cancelUrl: {
                type: String,
                default: () => null,
            },
            dateFormat: {
                type: String,
                default: () => 'Y-m-d',
                required: true
            },
        },
        mounted: function () {
            this.discountUnit = this.currency;
            this.value_label = this.__('discount.discount');
            this.type_options = this.__('enums.typeOptions');
        },
        methods: {
            generateCouponCode: function (event) {
                event.preventDefault();
                let context = this;
                axios
                    .post(this.generateUrl)
                    .then(res => {
                        context.code = res.data.data;
                        context.title = null;
                        $('.coupon-code-input').closest('div').find('.invalid-feedback').remove();
                    })
                    .catch(res => {
                        Botble.handleError(res.response.data);
                    });
            },
            handleChangeTypeOption: function () {
                let context = this;

                context.discountUnit = this.currency;
                context.value_label = this.__('discount.discount');

                switch (context.type_option) {
                    case 'amount':
                        context.target = 'all-orders';
                        break;
                    case 'percentage':
                        context.target = 'all-orders';
                        context.discountUnit = '%';
                        break;
                    case 'shipping':
                        context.value_label = this.__('discount.when_shipping_fee_less_than');
                        break;
                    case 'same-price':
                        context.target = 'group-products';
                        context.value_label = this.__('discount.is');
                        context.getListProductCollections();
                        break;
                }
            }
        }
    }
</script>
