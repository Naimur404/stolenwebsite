'use strict'
$(document).ready(function() {
    const { productOptionLang, coreBaseLang, currentProductOption, options } = window.productOptions
    let productOptionForm = {
        productOptions: currentProductOption,
        init() {
            this.eventListeners()
            this.generateProductOption()
            this.sortable()
        },
        sortable() {
            $('.option-value-sortable tbody').sortable({
                stop: function() {
                    let idsInOrder = $('.option-value-sortable tbody').sortable('toArray', { attribute: 'data-index' })
                    idsInOrder.map(function(id, index) {
                        $('.option-row[data-index="' + id + '"]').find('.option-value-order').val(index)
                    })
                },
            })

            $('.accordion-product-option').sortable({
                stop: function() {
                    let idsInOrder = $('.accordion-product-option').sortable('toArray', { attribute: 'data-index' })
                    idsInOrder.map(function(id, index) {
                        $('.accordion-item[data-index="' + id + '"]').find('.option-order').val(index)
                    })
                },
            })

        },
        generateProductOption() {
            let self = this
            let html = ''
            this.productOptions.map(function(item, index) {
                html += self.generateOptionTemplate(item, index)
            })
            $('#accordion-product-option').html(html)
            this.sortable()
        },
        eventListeners() {
            let self = this
            $('.product-option-form-wrap')
                .on('click', '.add-from-global-option', function() {
                    let selectedOption = $('#global-option').val()
                    if (selectedOption != -1) {
                        self.addFromGlobalOption(selectedOption)
                    } else {
                        toastr.error(productOptionLang.please_select_option)
                    }

                    return false
                })
                .on('click', '.remove-option', function() {
                    const index = $(this).data('index')
                    self.productOptions.splice(index, 1)
                    $(this).parents('.accordion-item').remove()
                })
                .on('keyup', '.option-name', function() {
                    const index = $(this).parents('.accordion-item').data('product-option-index')
                    const name = $(this).val()
                    $(this).parents('.accordion-item').find('.accordion-button').text(name)
                    self.productOptions[index].name = name
                })

                .on('change', '.option-type', function() {
                    const index = $(this).parents('.accordion-item').data('product-option-index')
                    self.productOptions[index].option_type = $(this).val()
                    self.generateProductOption()
                })

                .on('change', '.option-required', function() {
                    const index = $(this).parents('.accordion-item').data('product-option-index')
                    self.productOptions[index].required = $(this).is(':checked')
                })

                .on('click', '.add-new-row', function() {
                    self.addNewRow($(this))
                })

                .on('click', '.remove-row', function() {
                    $(this).parent().parent().remove()
                })

                .on('click', '.add-new-option', function() {
                    const option = {
                        name: '',
                        values: [{
                            affect_price: 0,
                            affect_type: 0,
                        }],
                        option_type: 'N/A',
                        required: false,
                    }

                    self.productOptions.push(option)

                    const html = self.generateOptionTemplate(option, self.productOptions.length - 1)
                    $('#accordion-product-option').append(html)

                    self.sortable()
                })
        },
        addNewRow(element) {
            let table = element.parent().find('table tbody')
            let index = element.parents('.accordion-item').data('product-option-index')
            let tr = table.find('tr').last().clone()
            let labelName = 'options[' + index + '][values][' + table.find('tr').length + '][option_value]',
                affectName = 'options[' + index + '][values][' + table.find('tr').length + '][affect_price]',
                affectTypeName = 'options[' + index + '][values][' + table.find('tr').length + '][affect_type]'
            tr.find('.option-label').prop('name', labelName).val('')
            tr.find('.affect_price').prop('name', affectName).val(0)
            tr.find('.affect_type').prop('name', affectTypeName).val(0)
            tr.find('.option-value-order').val(table.find('tr').length)
            tr.attr('data-index', table.find('tr').length)
            table.append(tr)
        },
        addFromGlobalOption(optionId) {
            let self = this
            axios
                .get(window.productOptions.routes.ajax_option_info + '?id=' + optionId)
                .then(function(res) {
                    const data = res.data.data

                    const option = {
                        id: data.id,
                        name: data.name,
                        option_type: data.option_type,
                        option_value: data.option_value,
                        values: data.values,
                        required: data.required,
                    }

                    self.productOptions.push(option)

                    const html = self.generateOptionTemplate(option, self.productOptions.length - 1)

                    $('#accordion-product-option').append(html)
                })
        },
        generateOptionTemplate(option, index) {
            let options = this.generateFieldOptions(option)
            let id = typeof option.id !== 'undefined' ? option.id : 0
            const order = typeof option.order !== 'undefined' && option.order != 9999 ? option.order : index
            const template = $(document).find('#template-option').html()
            const checked = (option.required) ? 'checked' : ''
            const values = this.generateOptionValues(option.values, option.option_type, index)
            return template.replace(/__index__/g, index)
                .replace(/__order__/g, order)
                .replace(/__id__/g, id)
                .replace(/__optionName__/g, '#' + (parseInt(index) + 1) + ' ' + option.name)
                .replace(/__nameLabel__/g, coreBaseLang.name)
                .replace(/__option_name__/g, option.name)
                .replace(/__namePlaceHolder__/g, coreBaseLang.name_placeholder)
                .replace(/__optionTypeLabel__/g, productOptionLang.option_type)
                .replace(/__optionTypeOption__/g, options)
                .replace(/__checked__/g, checked)
                .replace(/__requiredLabel__/g, productOptionLang.required)
                .replace(/__optionValueSortable__/g, values)
        },
        generateFieldOptions(option) {
            let html = ''
            $.each(options, function(key, value) {
                if (typeof value == 'object') {
                    html += '<optgroup label="' + key + '">'
                    $.each(value, function(option_key, option_value) {
                        const option_checked = (option.option_type === option_key) ? 'selected' : ''
                        html += '<option ' + option_checked + ' value="' + option_key + '">' + option_value + '</option>'
                    })
                    html += '</optgroup>'
                } else {
                    const option_checked = (option.option_type === key) ? 'selected' : ''
                    html += '<option ' + option_checked + ' value="' + key + '">' + value + '</option>'
                }
            })

            return html
        },
        generateOptionValues(values, type = '', index) {
            let label = productOptionLang.label,
                price = productOptionLang.price,
                priceType = productOptionLang.price_type,
                template = '',
                html = ''
            let optionType = type.split('\\')
            optionType = optionType[optionType.length - 1]
            if (optionType !== '' && typeof type !== 'undefined' && type !== 'N/A') {
                if (optionType === 'Field') {
                    template = $('#template-option-values-of-field').html()
                    const selectedFixed = (values[0].affect_type === 0) ? 'selected' : ''
                    const selectedPercent = (values[0].affect_type === 1) ? 'selected' : ''
                    html += template.replace(/__priceLabel__/g, price)
                        .replace(/__priceTypeLabel__/g, priceType)
                        .replace(/__id__/g, values[0].id)
                        .replace(/__index__/g, index)
                        .replace(/__affectPrice__/g, values[0].affect_price)
                        .replace(/__affectPriceLabel__/g, productOptionLang.affect_price_label)
                        .replace(/__selectedFixed__/g, selectedFixed)
                        .replace(/__fixedLang__/g, productOptionLang.fixed)
                        .replace(/__selectedPercent__/g, selectedPercent)
                        .replace(/__percentLang__/g, productOptionLang.percent)
                } else {
                    if (values.length > 0) {
                        const template = $('#template-option-type-array').html()
                        let valuesResult = ''
                        const tmp = template.replace(/__priceLabel__/g, price)
                            .replace(/__priceTypeLabel__/g, priceType)
                            .replace(/__index__/g, index)
                            .replace(/__label__/g, label)
                        $.each(values, function(key, value) {
                            const valueTemplate = $('#template-option-type-value').html()
                            const order = typeof value.order === 'undefined' ? value.order : key
                            const selectedFixed = (value.affect_type === 0) ? 'selected' : ''
                            const selectedPercent = (value.affect_type === 1) ? 'selected' : ''
                            valuesResult += valueTemplate
                                .replace(/__key__/g, key)
                                .replace(/__id__/g, value.id)
                                .replace(/__order__/g, order)
                                .replace(/__index__/g, index)
                                .replace(/__labelPlaceholder__/g, productOptionLang.label_placeholder)
                                .replace(/__affectPriceLabel__/g, productOptionLang.affect_price_label)
                                .replace(/__selectedFixed__/g, selectedFixed)
                                .replace(/__fixedLang__/g, productOptionLang.fixed)
                                .replace(/__selectedPercent__/g, selectedPercent)
                                .replace(/__option_value_input__/g, value.option_value ? value.option_value : '')
                                .replace(/__affectPrice__/g, value.affect_price)
                                .replace(/__percentLang__/g, productOptionLang.percent)
                        })
                        html += tmp.replace(/__optionValue__/g, valuesResult)
                    }
                    html += `<button type="button" class="btn btn-info mt-3 add-new-row" id="add-new-row">${productOptionLang.add_new_row}</button>`
                }
            }

            return html
        },
    }
    productOptionForm.init()
})
