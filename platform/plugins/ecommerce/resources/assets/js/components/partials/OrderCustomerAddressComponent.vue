<template>
    <div>
        <ec-modal id='add-customer' :title="__('order.create_new_customer')" :ok-title="__('order.save')"
                 :cancel-title="__('order.cancel')"
                 @shown='loadCountries($event)' @ok="$emit('create-new-customer', $event)">
            <div class='next-form-section'>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.name') }}</label>
                        <input type='text' class='next-input' v-model='child_customer_address.name'>
                    </div>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.phone') }}</label>
                        <input type='text' class='next-input' v-model='child_customer_address.phone'>
                    </div>
                </div>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.address') }}</label>
                        <input type='text' class='next-input' v-model='child_customer_address.address'>
                    </div>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.email') }}</label>
                        <input type='text' class='next-input' v-model='child_customer_address.email'>
                    </div>
                </div>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.country') }}</label>
                        <div class='ui-select-wrapper'>
                            <select class='ui-select' v-model='child_customer_address.country'
                                    @change='loadStates($event)'>
                                <option v-for='(countryName, countryCode) in countries' :value='countryCode'
                                        v-bind:key='countryCode'>
                                    {{ countryName }}
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
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.state') }}</label>
                        <div class='ui-select-wrapper' v-if='use_location_data'>
                            <select class='ui-select customer-address-state' v-model='child_customer_address.state'
                                    @change='loadCities($event)'>
                                <option v-for='state in states' :value='state.id' v-bind:key='state.id'>
                                    {{ state.name }}
                                </option>
                            </select>
                            <svg class='svg-next-icon svg-next-icon-size-16'>
                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'>
                                    <path d='M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z'></path>
                                </svg>
                            </svg>
                        </div>
                        <input type='text' class='next-input customer-address-state' v-if='!use_location_data'
                               v-model='child_customer_address.state'>
                    </div>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.city') }}</label>
                        <div class='ui-select-wrapper' v-if='use_location_data'>
                            <select class='ui-select customer-address-city' v-model='child_customer_address.city'>
                                <option v-for='city in cities' :value='city.id' v-bind:key='city.id'>{{
                                        city.name
                                    }}
                                </option>
                            </select>
                            <svg class='svg-next-icon svg-next-icon-size-16'>
                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'>
                                    <path d='M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z'></path>
                                </svg>
                            </svg>
                        </div>
                        <input type='text' class='next-input customer-address-city' v-if='!use_location_data'
                               v-model='child_customer_address.city'>
                    </div>
                </div>
                <div class='next-form-grid' v-if='zip_code_enabled'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.zip_code') }}</label>
                        <input type='text' class='next-input' v-model='child_customer_address.zip_code'>
                    </div>
                </div>
            </div>
        </ec-modal>

        <ec-modal id='edit-email' :title="__('order.update_email')" :ok-title="__('order.update')"
                 :cancel-title="__('order.close')"
                 @ok="$emit('update-customer-email', $event)">
            <div class='next-form-section'>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.email') }}</label>
                        <input class='next-input' v-model='child_customer_address.email'>
                    </div>
                </div>
            </div>
        </ec-modal>

        <ec-modal id='edit-address' :title="__('order.update_address')" :ok-title="__('order.save')"
                 :cancel-title="__('order.cancel')"
                 @shown='shownEditAddress' @ok="$emit('update-order-address', $event)">
            <div class='next-form-section'>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.name') }}</label>
                        <input type='text' class='next-input customer-address-name'
                               v-model='child_customer_address.name'>
                    </div>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.phone') }}</label>
                        <input type='text' class='next-input customer-address-phone'
                               v-model='child_customer_address.phone'>
                    </div>
                </div>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.address') }}</label>
                        <input type='text' class='next-input customer-address-address'
                               v-model='child_customer_address.address'>
                    </div>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.email') }}</label>
                        <input type='text' class='next-input customer-address-email'
                               v-model='child_customer_address.email'>
                    </div>
                </div>
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.country') }}</label>
                        <div class='ui-select-wrapper'>
                            <select class='ui-select customer-address-country' v-model='child_customer_address.country'
                                    @change='loadStates($event)'>
                                <option v-for='(countryName, countryCode) in countries'
                                        :selected='child_customer_address.country == countryCode' :value='countryCode'
                                        v-bind:key='countryCode'>
                                    {{ countryName }}
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
                <div class='next-form-grid'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.state') }}</label>
                        <div class='ui-select-wrapper' v-if='use_location_data'>
                            <select class='ui-select customer-address-state' v-model='child_customer_address.state'
                                    @change='loadCities($event)'>
                                <option v-for='state in states' :selected='child_customer_address.state == state.id'
                                        :value='state.id' v-bind:key='state.id'>
                                    {{ state.name }}
                                </option>
                            </select>
                            <svg class='svg-next-icon svg-next-icon-size-16'>
                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'>
                                    <path d='M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z'></path>
                                </svg>
                            </svg>
                        </div>
                        <input type='text' class='next-input customer-address-state' v-if='!use_location_data'
                               v-model='child_customer_address.state'>
                    </div>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.city') }}</label>
                        <div class='ui-select-wrapper' v-if='use_location_data'>
                            <select class='ui-select customer-address-city' v-model='child_customer_address.city'>
                                <option v-for='city in cities' :value='city.id' v-bind:key='city.id'>
                                    {{ city.name }}
                                </option>
                            </select>
                            <svg class='svg-next-icon svg-next-icon-size-16'>
                                <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'>
                                    <path d='M10 16l-4-4h8l-4 4zm0-12L6 8h8l-4-4z'></path>
                                </svg>
                            </svg>
                        </div>
                        <input type='text' class='next-input customer-address-city' v-if='!use_location_data'
                               v-model='child_customer_address.city'>
                    </div>
                </div>
                <div class='next-form-grid' v-if='zip_code_enabled'>
                    <div class='next-form-grid-cell'>
                        <label class='text-title-field'>{{ __('order.zip_code') }}</label>
                        <input type='text' class='next-input customer-address-zip-code'
                               v-model='child_customer_address.zip_code'>
                    </div>
                </div>
            </div>
        </ec-modal>
    </div>
</template>

<script>
export default {
    props: {
        child_customer_address: {
            type: Object,
            default: {},
        },
        zip_code_enabled: {
            type: Number,
            default: 0,
        },
        use_location_data: {
            type: Number,
            default: 0,
        },
    },
    data: function() {
        return {
            countries: [],
            states: [],
            cities: [],
        }
    },
    components: {},
    methods: {
        shownEditAddress: function($event) {
            this.loadCountries($event)

            if (this.child_customer_address.country) {
                this.loadStates($event, this.child_customer_address.country)
            }

            if (this.child_customer_address.state) {
                this.loadCities($event, this.child_customer_address.state)
            }
        },
        loadCountries: function() {
            let context = this
            if (_.isEmpty(context.countries)) {
                axios
                    .get(route('ajax.countries.list'))
                    .then(res => {
                        context.countries = res.data.data
                    })
                    .catch(res => {
                        Botble.handleError(res.response.data)
                    })
            }
        },
        loadStates: function($event, country_id) {
            if (!this.use_location_data) {
                return false
            }

            let context = this
            axios
                .get(route('ajax.states-by-country', { country_id: country_id || $event.target.value }))
                .then(res => {
                    context.states = res.data.data
                })
                .catch(res => {
                    Botble.handleError(res.response.data)
                })
        },
        loadCities: function($event, state_id) {
            if (!this.use_location_data) {
                return false
            }

            let context = this
            axios
                .get(route('ajax.cities-by-state', { state_id: state_id || $event.target.value }))
                .then(res => {
                    context.cities = res.data.data
                })
                .catch(res => {
                    Botble.handleError(res.response.data)
                })
        },
    },
}
</script>
