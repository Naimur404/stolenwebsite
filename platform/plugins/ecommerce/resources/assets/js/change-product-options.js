'use strict'
import forEach from 'lodash/forEach'

class FrontendProductOption {
    constructor() {
        this.priceSale = $('.product-details-content .product-price-sale .js-product-price')
        this.priceOriginal = $('.product-details-content .product-price-original .js-product-price')
        let priceElement = this.priceOriginal
        if (!this.priceSale.hasClass('d-none')) {
            priceElement = this.priceSale
        }
        this.basePrice = parseFloat(priceElement.text().replaceAll('$', ''))
        this.priceElement = priceElement
        this.extraPrice = {}
        this.eventListeners()
        this.formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        })
    }

    eventListeners() {
        $('.product-option .form-radio input').change((e) => {
            const name = $(e.target).attr('name')
            this.extraPrice[name] = parseFloat($(e.target).attr('data-extra-price'))
            this.changeDisplayedPrice()
        })

        $('.product-option .form-checkbox input').change((e) => {
            const name = $(e.target).attr('name')
            const extraPrice = parseFloat($(e.target).attr('data-extra-price'))
            if (typeof this.extraPrice[name] == 'undefined') {
                this.extraPrice[name] = []
            }
            this.extraPrice[name].push(extraPrice)
            this.changeDisplayedPrice()
        })
    }

    changeDisplayedPrice() {
        let extra = 0
        forEach(this.extraPrice, (value) => {
            if (typeof value == 'number') {
                extra = extra + value
            } else if (typeof value == 'object') {
                value.map((sub_value) => {
                    extra = extra + sub_value
                })
            }
        })
    }
}

$(document).ready(() => {
    new FrontendProductOption()
})
