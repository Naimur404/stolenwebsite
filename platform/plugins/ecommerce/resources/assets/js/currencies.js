class Currencies {
    constructor() {
        this.template = $('#currency_template').html()
        this.totalItem = 0

        this.deletedItems = []

        this.initData()
        this.handleForm()
        this.updateCurrency()
        this.clearCacheRates()
        this.switchApiProvider()
        this.changeOptionUsingExchangeRateCurrencyFormAPI()
    }

    initData() {
        let _self = this
        let data = $.parseJSON($('#currencies').html())

        $.each(data, (index, item) => {
            let template = _self.template
                .replace(/__id__/gi, item.id)
                .replace(/__position__/gi, item.order)
                .replace(/__isPrefixSymbolChecked__/gi, (item.is_prefix_symbol == 1 ? 'selected' : ''))
                .replace(/__notIsPrefixSymbolChecked__/gi, (item.is_prefix_symbol == 0 ? 'selected' : ''))
                .replace(/__isDefaultChecked__/gi, (item.is_default == 1 ? 'checked' : ''))
                .replace(/__title__/gi, item.title)
                .replace(/__decimals__/gi, item.decimals)
                .replace(/__exchangeRate__/gi, item.exchange_rate)
                .replace(/__symbol__/gi, item.symbol)

            $('.swatches-container .swatches-list').append(template)

            _self.totalItem++
        })
    }

    addNewAttribute() {
        let _self = this

        let template = _self.template
            .replace(/__id__/gi, 0)
            .replace(/__position__/gi, (_self.totalItem))
            .replace(/__isPrefixSymbolChecked__/gi, '')
            .replace(/__notIsPrefixSymbolChecked__/gi, '')
            .replace(/__isDefaultChecked__/gi, (_self.totalItem == 0 ? 'checked' : ''))
            .replace(/__title__/gi, '')
            .replace(/__decimals__/gi, 0)
            .replace(/__exchangeRate__/gi, 1)
            .replace(/__symbol__/gi, '')


        $('.swatches-container .swatches-list').append(template)

        _self.totalItem++
    }

    exportData() {
        let data = []

        $('.swatches-container .swatches-list li').each((index, item) => {
            let $current = $(item)
            data.push({
                id: $current.data('id'),
                is_default: ($current.find('[data-type=is_default] input[type=radio]').is(':checked') ? 1 : 0),
                order: $current.index(),
                title: $current.find('[data-type=title] input').val(),
                symbol: $current.find('[data-type=symbol] input').val(),
                decimals: $current.find('[data-type=decimals] input').val(),
                exchange_rate: $current.find('[data-type=exchange_rate] input').val(),
                is_prefix_symbol: $current.find('[data-type=is_prefix_symbol] select').val(),
            })
        })

        return data
    }

    handleForm() {
        let _self = this

        $('.swatches-container .swatches-list').sortable()

        $('body')
            .on('submit', '.main-setting-form', () => {
                let data = _self.exportData()

                $('#currencies').val(JSON.stringify(data))

                $('#deleted_currencies').val(JSON.stringify(_self.deletedItems))
            })
            .on('click', '.js-add-new-attribute', event => {
                event.preventDefault()

                _self.addNewAttribute()
            })
            .on('click', '.swatches-container .swatches-list li .remove-item a', event => {
                event.preventDefault()

                let $item = $(event.currentTarget).closest('li')

                _self.deletedItems.push($item.data('id'))

                $item.remove()
            })
    }

    updateCurrency() {
        $(document).on('click', '#btn-update-currencies', (event) => {
            event.preventDefault()
            let _self = $(event.currentTarget)

            const form = $('.main-setting-form')

            $.ajax({
                type: 'POST',
                url: form.prop('url'),
                data: form.serialize(),
                success: (res) => {
                    if (res.error) {
                        Botble.showNotice('error', res.message)
                    }
                },
            })

            $.ajax({
                type: 'POST',
                url: _self.data('url'),
                beforeSend: () => {
                    _self.addClass('button-loading')
                },
                success: (res) => {
                    if (!res.error) {
                        Botble.showNotice('success', res.message)
                        const data = res.data
                        const template = $('#currency_template').html()
                        let html = ''
                        $('#loading-update-currencies').show()
                        $.each(data, (index, item) => {
                            html += template
                                .replace(/__id__/gi, item.id)
                                .replace(/__position__/gi, item.order)
                                .replace(/__isPrefixSymbolChecked__/gi, (item.is_prefix_symbol == 1 ? 'selected' : ''))
                                .replace(/__notIsPrefixSymbolChecked__/gi, (item.is_prefix_symbol == 0 ? 'selected' : ''))
                                .replace(/__isDefaultChecked__/gi, (item.is_default == 1 ? 'checked' : ''))
                                .replace(/__title__/gi, item.title)
                                .replace(/__decimals__/gi, item.decimals)
                                .replace(/__exchangeRate__/gi, item.exchange_rate)
                                .replace(/__symbol__/gi, item.symbol)

                        })
                        setTimeout(() => {
                            $('.swatches-container .swatches-list').html(html)
                        }, 1000)
                    } else {
                        Botble.showNotice('error', res.message)
                    }
                },
                error: (res) => {
                    Botble.handleError(res)
                    _self.removeClass('button-loading')
                },
                complete: () => {
                    _self.removeClass('button-loading')
                },
            })
        })
    }

    clearCacheRates() {
        $(document).on('click', '#btn-clear-cache-rates', (event) => {
            event.preventDefault()

            let _self = $(event.currentTarget)

            $.ajax({
                type: 'POST',
                url: _self.data('url'),
                beforeSend: () => {
                    _self.addClass('button-loading')
                },
                success: (res) => {
                    if (!res.error) {
                        Botble.showNotice('success', res.message)
                    } else {
                        Botble.showNotice('error', res.message)
                    }
                },
                error: (res) => {
                    Botble.handleError(res)
                    _self.removeClass('button-loading')
                },
                complete: () => {
                    _self.removeClass('button-loading')
                },
            })
        })
    }

    switchApiProvider() {
        $(document).on('change', '.switch_api_provider', (event) => {
            event.preventDefault()
            const apiLayer = $('.api-layer-api-key')
            const openExchange = $('.open-exchange-api-key')
            if (event.target.value === 'api_layer') {
                apiLayer.show()
                openExchange.hide()
            } else if (event.target.value === 'open_exchange_rate') {
                openExchange.show()
                apiLayer.hide()
            }
        })
    }

    changeOptionUsingExchangeRateCurrencyFormAPI() {
        $(document).on('change', 'input[name="use_exchange_rate_from_api"]', (event) => {
            event.preventDefault()

            const inputExchangeRate = $('.swatch-exchange-rate').find('.input-exchange-rate')

            if (event.target.value === '1') {
                inputExchangeRate.attr('disabled', 'disabled')
            } else {
                inputExchangeRate.removeAttr('disabled')
            }
        })

    }
}

$(window).on('load', () => {
    new Currencies()
})
