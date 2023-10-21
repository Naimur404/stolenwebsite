<template>
    <div>
        <div v-for='option in options' v-bind:key='option.id'>
            <label :class="{'required': option.required}">{{ option.name }}</label>
            <div v-if="option.option_type == 'dropdown'">
                <select class='form-select' @input='changeInput($event, option, value)'>
                    <option value=''>{{ __('order.select_one') }}</option>
                    <option v-for='value in option.values' v-bind:key='value.option_value'>{{ value.title }}</option>
                </select>
            </div>
            <div v-if="option.option_type == 'checkbox'">
                <div class='form-check' v-for='value in option.values' v-bind:key='value.id'>
                    <input class='form-check-input' type='checkbox' :name="'option-' + option.id"
                           @input='changeInput($event, option, value)' :value='value.option_value'
                           :id="'form-check-' + value.id">
                    <label class='form-check-label' :for="'form-check-' + value.id">{{ value.title }}</label>
                </div>
            </div>
            <div v-if="option.option_type == 'radio'">
                <div class='form-check' v-for='value in option.values' v-bind:key='value.id'>
                    <input class='form-check-input' type='radio' :name="'option-' + option.id"
                           @input='changeInput($event, option, value)' :value='value.option_value'
                           :id="'form-check-' + value.id">
                    <label class='form-check-label' :for="'form-check-' + value.id">{{ value.title }}</label>
                </div>
            </div>
            <div v-if="option.option_type == 'field'">
                <div class='form-floating mb-3' v-for='value in option.values' v-bind:key='value.id'>
                    <input type='text' class='form-control' @input='changeInput($event, option, value)'
                           :name="'option-' + option.id" :id="'form-input-' + value.id" placeholder='...'>
                    <label :for="'form-input-' + value.id">{{ value.title || __('order.enter_free_text') }}</label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        options: {
            type: Array,
            default: [],
            required: true,
        },
        product: {
            type: Object,
            default: {},
            required: false,
        },
    },
    data: function() {
        return {
            values: [],
        }
    },
    methods: {
        changeInput: function($event, option, value) {
            if (!this.values[option.id]) {
                this.values[option.id] = {}
            }
            this.values[option.id] = $event.target.value
        },
    },
}
</script>
