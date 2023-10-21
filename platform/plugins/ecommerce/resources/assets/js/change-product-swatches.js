class ChangeProductSwatches {
    constructor() {
        this.xhr = null

        this.handleEvents()
    }

    handleEvents() {
        let _self = this
        let $body = $('body')

        $body.on('click', '.product-attributes .visual-swatch label, .product-attributes .text-swatch label', e => {
            e.preventDefault()
            let $this = $(e.currentTarget)
            let $radio = $this.find('input[type=radio]')

            if ($radio.is(':checked')) {
                return false
            }

            $radio.prop('checked', true)

            if ($this.closest('.visual-swatch').find('input[type=radio]:checked').length < 1) {
                $radio.prop('checked', true)
            }

            $radio.trigger('change')
        })

        $body.off('change', '.product-attributes input, .product-attributes select')
            .on('change', '.product-attributes input, .product-attributes select', event => {
                let $this = $(event.currentTarget)

                let $parent = $this.closest('.product-attributes')
                _self.getProductVariation($parent)
            })

        if ($('.product-attribute-swatches').length) {
            window.addEventListener(
                'popstate',
                function(e) {
                    if (e.state?.product_attributes_id) {
                        let $el = $('#' + e.state.product_attributes_id)

                        if (window.onChangeSwatchesSuccess && typeof window.onChangeSwatchesSuccess === 'function') {
                            window.onChangeSwatchesSuccess(e.state.data, $el)
                        }

                        if (e.state.slugAttributes) {
                            _self.updateSelectingAttributes(e.state.slugAttributes, $el)
                        }
                    } else {
                        $('.product-attribute-swatches').each(function(i, el) {
                            let params = _self.parseParamsSearch()
                            let attributes = []
                            let slugAttributes = {}
                            let $el = $(el)

                            if (params && Object.keys(params).length) {
                                $.each(params, function(key, slug) {
                                    let $parent = $el.find('.attribute-swatches-wrapper[data-slug=' + key + ']')
                                    if ($parent.length) {
                                        let value
                                        if ($parent.data('type') == 'dropdown') {
                                            value = $parent.find('option[data-slug=' + slug + ']').val()
                                        } else {
                                            value = $parent.find('input[data-slug=' + slug + ']').val()
                                        }

                                        if (value) {
                                            attributes.push(value)
                                            slugAttributes[key] = value
                                        }
                                    }
                                })
                            }

                            _self.callAjax(attributes, $el, slugAttributes, false)
                        })
                    }
                },
                false,
            )
        }
    }

    getProductVariation($productAttributes) {
        let _self = this

        let attributes = []
        let slugAttributes = {}

        /**
         * Break current request
         */
        if (_self.xhr) {
            _self.xhr.abort()

            _self.xhr = null
        }

        /**
         * Get attributes
         */
        let $attributeSwatches = $productAttributes.find('.attribute-swatches-wrapper')
        $attributeSwatches.each((index, el) => {
            let $current = $(el)

            let $input
            if ($current.data('type') === 'dropdown') {
                $input = $current.find('select option:selected')
            } else {
                $input = $current.find('input[type=radio]:checked')
            }

            let slug = $input.data('slug')
            let value = $input.val()

            if (value) {
                let setSlug = $current.find('.attribute-swatch').data('slug')
                slugAttributes[setSlug] = slug
                attributes.push(value)
            }
        })

        _self.callAjax(attributes, $productAttributes, slugAttributes)
    }

    callAjax = function(attributes, $productAttributes, slugAttributes, updateUrl = true) {
        let _self = this
        let formData = {
            attributes,
            _: +new Date(),
        }

        let id = $productAttributes.attr('id')

        _self.xhr = $.ajax({
            url: $productAttributes.data('target'),
            type: 'GET',
            data: formData,
            beforeSend: () => {
                if (window.onBeforeChangeSwatches && typeof window.onBeforeChangeSwatches === 'function') {
                    window.onBeforeChangeSwatches(attributes, $productAttributes)
                }
            },
            success: res => {
                if (window.onChangeSwatchesSuccess && typeof window.onChangeSwatchesSuccess === 'function') {
                    window.onChangeSwatchesSuccess(res, $productAttributes)
                }

                if (!res.data.error_message) {
                    if (res.data.selected_attributes) {
                        slugAttributes = {}
                        $.each(res.data.selected_attributes, (index, item) => {
                            slugAttributes[item.set_slug] = item.slug
                        })
                    }

                    const url = new URL(window.location)

                    _self.updateSelectingAttributes(slugAttributes, $('#' + id))

                    $.each(slugAttributes, (name, value) => {
                        url.searchParams.set(name, value)
                    })

                    if (updateUrl && url != window.location.href) {
                        window.history.pushState(
                            { formData, data: res, product_attributes_id: id, slugAttributes },
                            res.message,
                            url,
                        )
                    } else {
                        window.history.replaceState(
                            { formData, data: res, product_attributes_id: id, slugAttributes },
                            res.message,
                            url,
                        )
                    }
                }
            },
            complete: res => {
                if (window.onChangeSwatchesComplete && typeof window.onChangeSwatchesComplete === 'function') {
                    window.onChangeSwatchesComplete(res, $productAttributes)
                }
            },
            error: res => {
                if (window.onChangeSwatchesError && typeof window.onChangeSwatchesError === 'function') {
                    window.onChangeSwatchesError(res, $productAttributes)
                }
            },
        })
    }

    updateSelectingAttributes = function(slugAttributes, $el) {
        $.each(slugAttributes, function(key, slug) {
            let $parent = $el.find('.attribute-swatches-wrapper[data-slug=' + key + ']')

            if ($parent.length) {
                if ($parent.data('type') == 'dropdown') {
                    let selected = $parent.find('select option[data-slug=' + slug + ']').val()
                    $parent.find('select').val(selected)
                } else {
                    $parent.find('input:checked').prop('checked', false)
                    $parent.find('input[data-slug=' + slug + ']').prop('checked', true)
                }
            }
        })
    }

    parseParamsSearch = function(query, includeArray = false) {
        let pairs = query || window.location.search.substring(1)
        let re = /([^&=]+)=?([^&]*)/g
        let decodeRE = /\+/g  // Regex for replacing addition symbol with a space
        let decode = function(str) {
            return decodeURIComponent(str.replace(decodeRE, ' '))
        }
        let params = {}, e
        while (e = re.exec(pairs)) {
            let k = decode(e[1]), v = decode(e[2])
            if (k.substring(k.length - 2) == '[]') {
                if (includeArray) {
                    k = k.substring(0, k.length - 2)
                }
                (params[k] || (params[k] = [])).push(v)
            } else params[k] = v
        }
        return params
    }
}

$(() => {
    new ChangeProductSwatches()
})
