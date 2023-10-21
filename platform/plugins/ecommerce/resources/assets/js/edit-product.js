class EcommerceProduct {
    constructor() {
        this.$body = $('body')

        this.initElements()
        this.handleEvents()
        this.handleChangeSaleType()
        this.handleShipping()
        this.handleStorehouse()

        this.handleModifyAttributeSets()
        this.handleAddVariations()
        this.handleDeleteVariations()
        this.setProductVariationDefault()

        window.productAttributeSets = []
        window.loadedProductAttributeSets = false

        this.productAttributeSets()

        let pageSizeSelect2 = 50

        $.fn.select2.amd.require(
            ['select2/data/array', 'select2/utils'],
            function(ArrayData, Utils) {
                function CustomData($element, options) {
                    CustomData.__super__.constructor.call(this, $element, options)
                }

                Utils.Extend(CustomData, ArrayData)

                CustomData.prototype.query = function(params, callback) {
                    let $this = this
                    results = []
                    if (params.term && params.term !== '') {
                        results = _.filter($this.options.options.data, function(e) {
                            return e.text.toUpperCase().indexOf(params.term.toUpperCase()) >= 0
                        })
                    } else {
                        results = $this.options.options.data
                    }

                    if (!('page' in params)) {
                        params.page = 1
                    }
                    let data = {
                        results: results.slice((params.page - 1) * pageSizeSelect2, params.page * pageSizeSelect2),
                        pagination: {
                            more: params.page * pageSizeSelect2 < results.length,
                        },
                    }
                    callback(data)
                }

                window.CustomDataApdapterSelect2 = CustomData
            },
        )
    }

    productAttributeSets() {
        let url = $('.product-attribute-sets-url').data('url')
        if (url) {
            $.ajax({
                url,
                success: (res) => {
                    if (res.error == false) {
                        window.productAttributeSets = res.data
                        window.loadedProductAttributeSets = true
                    } else {
                        window.loadedProductAttributeSets = null
                    }
                },
                error: (res) => {
                    Botble.handleError(res)
                },
            })
        } else {
            window.loadedProductAttributeSets = true
        }
    }

    handleEvents() {
        let _self = this

        _self.$body.on('click', '.select-all', event => {
            event.preventDefault()
            let $select = $($(event.currentTarget).attr('href'))
            $select.find('option').attr('selected', true)
            $select.trigger('change')
        })

        _self.$body.on('click', '.deselect-all', event => {
            event.preventDefault()
            let $select = $($(event.currentTarget).attr('href'))
            $select.find('option').removeAttr('selected')
            $select.trigger('change')
        })

        _self.$body.on('change', '#attribute_sets', event => {
            let $groupContainer = $('#attribute_set_group')

            let value = $(event.currentTarget).val()

            $groupContainer.find('.panel').hide()

            if (value) {
                _.forEach(value, value => {
                    $groupContainer.find('.panel[data-id="' + value + '"]').show()
                })
            }
            $('.select2-select').select2()
        })

        $('#attribute_sets').trigger('change')

        _self.$body.on('change', '.is-variation-default input', event => {
            let $current = $(event.currentTarget)
            let isChecked = $current.is(':checked')
            $('.is-variation-default input').prop('checked', false)
            if (isChecked) {
                $current.prop('checked', true)
            }
        })

        $(document).on('change', '.table-check-all', event => {
            let $current = $(event.currentTarget)
            if ($current.prop('checked')) {
                $('.btn-trigger-delete-selected-variations').show()
            } else {
                $('.btn-trigger-delete-selected-variations').hide()
            }

        })

        $(document).on('change', '.checkboxes', event => {
            let $current = $(event.currentTarget)
            let $table = $current.closest('.table-hover-variants')

            if ($table.find('.checkboxes:checked').length > 0) {
                $('.btn-trigger-delete-selected-variations').show()
            } else {
                $('.btn-trigger-delete-selected-variations').hide()
            }
        })

        $(document).on('click', '.btn-trigger-delete-selected-variations', event => {
            event.preventDefault()
            let $current = $(event.currentTarget)

            let ids = []
            $('.table-hover-variants').find('.checkboxes:checked').each((i, el) => {
                ids[i] = $(el).val()
            })

            if (ids.length === 0) {
                Botble.showError(BotbleVariables.languages.tables.please_select_record)
                return false
            }

            $('#delete-selected-variations-button').data('href', $current.data('target'))

            $('#delete-variations-modal').modal('show')
        })

        $('#delete-selected-variations-button').off('click').on('click', event => {
            event.preventDefault()

            let $current = $(event.currentTarget)

            $current.addClass('button-loading')

            let $table = $('.table-hover-variants')

            let ids = []
            $table.find('.checkboxes:checked').each((i, el) => {
                ids[i] = $(el).val()
            })

            $.ajax({
                url: $current.data('href'),
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    ids,
                },
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        Botble.showSuccess(res.message)
                        _self.afterDeleteVersion(res, $table)

                        $('.btn-trigger-delete-selected-variations').hide()
                        $current.closest('.modal').modal('hide')
                    }
                },
                error: data => {
                    Botble.handleError(data)
                },
                complete: () => {
                    $current.removeClass('button-loading')
                },
            })
        })
    }

    afterDeleteVersion(res, $table) {
        if (!$table) {
            $table = $('#product-variations-wrapper').find('table')
        }

        if (res.data.total_product_variations == 0) {
            let _self = this
            $('#main-manage-product-type').load(window.location.href + ' #main-manage-product-type > *', () => {
                _self.initElements()
                _self.handleEvents()
            })
        } else if ($table.length) {
            window.LaravelDataTables && LaravelDataTables[$table.attr('id')] && LaravelDataTables[$table.attr('id')].draw()
        }
    }

    initElements() {
        $('.select2-select').select2()

        $('.form-date-time').datetimepicker({
            format: 'YYYY-MM-DD HH:mm:ss',
            toolbarPlacement: 'bottom',
            showTodayButton: true,
            stepping: 1,
        })

        $('#attribute_set_group .panel-collapse').on('shown.bs.collapse', () => {
            $('.select2-select').select2()
        })

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', () => {
            $('.select2-select').select2()
        })
    }

    handleChangeSaleType() {
        let _self = this

        _self.$body.on('click', '.turn-on-schedule', event => {
            event.preventDefault()
            let $current = $(event.currentTarget)
            let $group = $current.closest('.price-group')
            $current.addClass('hidden')
            $group.find('.turn-off-schedule').removeClass('hidden')
            $group.find('.detect-schedule').val(1)
            $group.find('.scheduled-time').removeClass('hidden')
        })

        _self.$body.on('click', '.turn-off-schedule', event => {
            event.preventDefault()
            let $current = $(event.currentTarget)
            let $group = $current.closest('.price-group')
            $current.addClass('hidden')
            $group.find('.turn-on-schedule').removeClass('hidden')
            $group.find('.detect-schedule').val(0)
            $group.find('.scheduled-time').addClass('hidden')
        })
    }

    handleStorehouse() {
        let _self = this

        _self.$body.on('click', 'input.storehouse-management-status', event => {
            let $storehouseInfo = $('.storehouse-info')
            if ($(event.currentTarget).prop('checked') === true) {
                $storehouseInfo.removeClass('hidden')
                $('.stock-status-wrapper').addClass('hidden')
            } else {
                $storehouseInfo.addClass('hidden')
                $('.stock-status-wrapper').removeClass('hidden')
            }
        })
    }

    handleShipping() {
        let _self = this

        _self.$body.on('click', '.change-measurement .dropdown-menu a', event => {
            event.preventDefault()
            let $current = $(event.currentTarget)
            let $parent = $current.closest('.change-measurement')
            let $input = $parent.find('input[type=hidden]')
            $input.val($current.attr('data-alias'))
            $parent.find('.dropdown-toggle .alias').html($current.attr('data-alias'))
        })
    }

    handleModifyAttributeSets() {
        let _self = this

        _self.$body.on('click', '#store-related-attributes-button', event => {
            event.preventDefault()

            let $current = $(event.currentTarget)

            let attributeSets = []
            $current.closest('.modal-content').find('.attribute-set-item:checked').each((index, item) => {
                attributeSets[index] = $(item).val()
            })

            $.ajax({
                url: $current.data('target'),
                type: 'POST',
                data: {
                    attribute_sets: attributeSets,
                },
                beforeSend: () => {
                    $current.addClass('button-loading')
                },
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        Botble.showSuccess(res.message)

                        $('#select-attribute-sets-modal').modal('hide')
                        $('form').removeClass('dirty')
                        window.location.reload()
                    }
                },
                error: data => {
                    Botble.handleError(data)
                },
                complete: () => {
                    $current.removeClass('button-loading')
                },
            })
        })
    }

    handleAddVariations() {
        let _self = this

        let createOrUpdateVariation = $current => {
            let $form = $current.closest('.modal-content').find('.variation-form-wrapper form')
            let formData = new FormData($form[0])
            if (jQuery().inputmask) {
                $form.find('input.input-mask-number').map(function(i, e) {
                    const $input = $(e)
                    if ($input.inputmask) {
                        formData.append($input.attr('name'), $input.inputmask('unmaskedvalue'))
                    }
                })
            }

            $.ajax({
                url: $current.data('target'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    $current.addClass('button-loading')
                },
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        Botble.showSuccess(res.message)
                        $current.closest('.modal.fade').modal('hide')

                        let $table = $('#product-variations-wrapper').find('table')
                        if ($table.length) {
                            window.LaravelDataTables && LaravelDataTables[$table.attr('id')] && LaravelDataTables[$table.attr('id')].draw()
                        }

                        $current.closest('.modal-content').find('.variation-form-wrapper').remove()
                    }
                },
                complete: () => {
                    $current.removeClass('button-loading')
                },
                error: data => {
                    Botble.handleError(data)
                },
            })
        }

        _self.$body.on('click', '#store-product-variation-button', event => {
            event.preventDefault()
            createOrUpdateVariation($(event.currentTarget))
        })

        _self.$body.on('click', '#update-product-variation-button', event => {
            event.preventDefault()
            createOrUpdateVariation($(event.currentTarget))
        })

        $('#add-new-product-variation-modal').on('hidden.bs.modal', function(e) {
            $(this).find('.modal-content .variation-form-wrapper').remove()
        })

        $('#edit-product-variation-modal').on('hidden.bs.modal', function(e) {
            $(this).find('.modal-content .variation-form-wrapper').remove()
        })

        _self.$body.on('click', '#generate-all-versions-button', event => {
            event.preventDefault()
            let $current = $(event.currentTarget)

            $.ajax({
                url: $current.data('target'),
                type: 'POST',
                beforeSend: () => {
                    $current.addClass('button-loading')
                },
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        Botble.showSuccess(res.message)

                        $('#generate-all-versions-modal').modal('hide')

                        window.LaravelDataTables[$('#product-variations-wrapper .dataTables_wrapper table').prop('id')].draw()
                    }
                },
                complete: () => {
                    $current.removeClass('button-loading')
                },
                error: data => {
                    Botble.handleError(data)
                },
            })
        })

        $(document).on('click', '.btn-trigger-add-new-product-variation', event => {
            event.preventDefault()
            let $current = $(event.currentTarget)

            $('#add-new-product-variation-modal .modal-body .loading-spinner').show()
            $('#add-new-product-variation-modal .modal-body .variation-form-wrapper').remove()
            $('#add-new-product-variation-modal').modal('show')

            $.ajax({
                url: $current.data('load-form'),
                type: 'GET',
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        $('#add-new-product-variation-modal .modal-body .loading-spinner').hide()
                        $('#add-new-product-variation-modal .modal-body').append(res.data)

                        $('#add-new-product-variation-modal .select2-attributes').map((index, el) => {
                            const $el = $(el)
                            let data = productAttributeSets.find((item) => item.id == $el.data('id'))

                            if (data) {
                                data = data.attributes.map((item, index) => {
                                    return { id: item.id, text: item.title }
                                })
                                $el.select2({
                                    data,
                                    ajax: {},
                                    dataAdapter: CustomDataApdapterSelect2,
                                    dropdownParent: $('#add-new-product-variation-modal'),
                                })
                            }
                        })

                        _self.initElements()
                        Botble.initResources()
                        $('#store-product-variation-button').data('target', $current.data('target'))

                        $('.list-gallery-media-images').each((index, item) => {
                            let $current = $(item)
                            if ($current.data('ui-sortable')) {
                                $current.sortable('destroy')
                            }
                            $current.sortable()
                        })
                    }
                },
                error: data => {
                    Botble.handleError(data)
                },
            })
        })

        $(document).on('click', '.btn-trigger-edit-product-version', event => {
            event.preventDefault()
            $('#update-product-variation-button').data('target', $(event.currentTarget).data('target'))
            let $current = $(event.currentTarget)

            $('#edit-product-variation-modal .modal-body .loading-spinner').show()
            $('#edit-product-variation-modal .modal-body .variation-form-wrapper').remove()
            $('#edit-product-variation-modal').modal('show')

            $.ajax({
                url: $current.data('load-form'),
                type: 'GET',
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        $('#edit-product-variation-modal .modal-body .loading-spinner').hide()
                        $('#edit-product-variation-modal .modal-body').append(res.data)

                        $('#edit-product-variation-modal .select2-attributes').map((index, el) => {
                            const $el = $(el)
                            let data = productAttributeSets.find((item) => item.id == $el.data('id'))

                            if (data) {
                                data = data.attributes.map((item, index) => {
                                    return { id: item.id, text: item.title }
                                })
                                $el.select2({
                                    data,
                                    ajax: {},
                                    dataAdapter: CustomDataApdapterSelect2,
                                    dropdownParent: $('#edit-product-variation-modal'),
                                })
                            }
                        })

                        _self.initElements()
                        Botble.initResources()
                        $('.list-gallery-media-images').each((index, item) => {
                            let $current = $(item)
                            if ($current.data('ui-sortable')) {
                                $current.sortable('destroy')
                            }
                            $current.sortable()
                        })
                    }
                },
                error: data => {
                    Botble.handleError(data)
                },
            })
        })

        _self.$body.on('click', '.btn-trigger-add-attribute-to-simple-product', event => {
            event.preventDefault()
            let $current = $(event.currentTarget)

            let addedAttributes = []
            let addedAttributeSets = []

            $.each($('.list-product-attribute-items-wrap .product-attribute-set-item'), (index, el) => {
                let val = $(el).find('.product-select-attribute-item').val()
                if (val !== '') {
                    addedAttributes.push($(el).find('.product-select-attribute-item-value').val())
                    addedAttributeSets.push(val)
                }
            })

            if (addedAttributes.length) {
                $.ajax({
                    url: $current.data('target'),
                    type: 'POST',
                    data: {
                        added_attributes: addedAttributes,
                        added_attribute_sets: addedAttributeSets,
                    },
                    beforeSend: () => {
                        $current.addClass('button-loading')
                    },
                    success: res => {
                        if (res.error) {
                            Botble.showError(res.message)
                        } else {
                            $('form').removeClass('dirty')
                            window.location.reload()
                            Botble.showSuccess(res.message)
                        }
                    },
                    complete: () => {
                        $current.removeClass('button-loading')
                    },
                    error: data => {
                        Botble.handleError(data)
                    },
                })
            }
        })
    }

    handleDeleteVariations() {
        let _self = this

        $(document).on('click', '.btn-trigger-delete-version', event => {
            event.preventDefault()
            $('#delete-version-button').data('target', $(event.currentTarget).data('target'))
                .data('id', $(event.currentTarget).data('id'))
            $('#confirm-delete-version-modal').modal('show')
        })

        _self.$body.on('click', '#delete-version-button', event => {
            event.preventDefault()
            let $current = $(event.currentTarget)

            $.ajax({
                url: $current.data('target'),
                type: 'POST',
                beforeSend: () => {
                    $current.addClass('button-loading')
                },
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        _self.afterDeleteVersion(res)

                        $('#confirm-delete-version-modal').modal('hide')
                        Botble.showSuccess(res.message)
                    }
                },
                complete: () => {
                    $current.removeClass('button-loading')
                },
                error: data => {
                    Botble.handleError(data)
                },
            })
        })
    }

    setProductVariationDefault = () => {
        $(document).on('click', '.table-hover-variants input[name=variation_default_id]', function(e) {
            let $this = $(e.currentTarget)
            let url = $this.data('url')
            if (url) {
                $.ajax({
                    url,
                    method: 'POST',
                    success: (res) => {
                        if (res.error) {
                            Botble.showError(res.message)
                        } else {
                            Botble.showSuccess(res.message)
                        }
                    },
                    error: (res) => {
                        Botble.handleError(res)
                    },
                })
            }
        })
    }

    static tableInitComplete = (table, settings) => {
        if (!settings.oInit.paging) {
            return
        }

        this.initHeaderFilterColumns(table, settings)
    }

    static initHeaderFilterColumns = (table, settings) => {
        let _self = this
        if (!window.loadedProductAttributeSets) {
            if (window.loadedProductAttributeSets == null) {
                return
            }

            setTimeout(() => {
                _self.initHeaderFilterColumns(table, settings)
            }, 1500)

            return
        }

        let tr = document.createElement('tr')
        $(tr).prop('role', 'row').addClass('dataTable-custom-filter')

        const searchDelay = settings.searchDelay || 0
        const _fnThrottle = $.fn.dataTable.util.throttle
        const wrapper = $(settings.nTableWrapper)

        const searchFn = function(column, val) {
            searchDelay ?
                _fnThrottle(function() {
                    column.search(val).draw()
                }, searchDelay)() :
                function() {
                    column.search(val).draw()
                }
        }

        table.columns().every(function(index) {
            const column = this

            const setting = column.settings()[0].aoColumns[index]
            const th = $(document.createElement('th')).appendTo($(tr))

            if (setting.searchable) {
                if (setting?.search_data?.type == 'customSelect') {
                    let select = $(
                        `<div><select class='form-select input-sm' data-placeholder='${setting.search_data.placeholder || 'Select'}'></select></div>`,
                    )

                    th.append(select)

                    select = th.find('select')

                    let attributeSet = productAttributeSets.find((item) => item.id == setting.search_data.attribute_set_id)

                    let data = [{ id: '', text: '' }]
                    if (attributeSet) {
                        data = data.concat(attributeSet.attributes.map((item, index) => {
                            return { id: item.id, text: item.title }
                        }))
                    }

                    select.select2({
                        data,
                        width: '100%',
                        dropdownAutoWidth: true,
                        allowClear: true,
                        ajax: {},
                        dataAdapter: CustomDataApdapterSelect2,
                    })

                    select.on('change', function() {
                        searchFn(column, $(this).val() || '')
                    })

                }
            }
            if (settings.oInit.responsive) {
                if (!column.responsiveHidden() || !column.visible()) {
                    th.hide()
                }
            }
        })

        $(tr).appendTo(wrapper.find('thead'))

        if (settings.oInit.responsive) {
            table.on('responsive-resize', function(e, dt, columns) {
                hideSearchInputs(columns)
            })

            function hideSearchInputs(columns) {
                for (let i = 0; i < columns.length; i++) {
                    if (columns[i]) {
                        wrapper
                            .find('.dataTable-custom-filter th:eq(' + i + ')')
                            .show()
                    } else {
                        wrapper
                            .find('.dataTable-custom-filter th:eq(' + i + ')')
                            .hide()
                    }
                }
            }
        }
    }
}

$(() => {
    new EcommerceProduct()
    window.EcommerceProduct = EcommerceProduct

    $('body').on('click', '.list-gallery-media-images .btn_remove_image', event => {
        event.preventDefault()
        $(event.currentTarget).closest('li').remove()
    })

    $(document).on('click', '.btn-trigger-select-product-attributes', event => {
        event.preventDefault()
        $('#store-related-attributes-button').data('target', $(event.currentTarget).data('target'))
        $('#select-attribute-sets-modal').modal('show')
    })

    $(document).on('click', '.btn-trigger-generate-all-versions', event => {
        event.preventDefault()
        $('#generate-all-versions-button').data('target', $(event.currentTarget).data('target'))
        $('#generate-all-versions-modal').modal('show')
    })

    $(document).on('click', '.btn-trigger-add-attribute', event => {
        event.preventDefault()

        $('.list-product-attribute-wrap').toggleClass('hidden')
        $('.list-product-attribute-values-wrap').toggleClass('hidden')

        let $this = $(event.currentTarget)
        $this.toggleClass('adding_attribute_enable text-warning')

        if ($this.hasClass('adding_attribute_enable')) {
            $('#is_added_attributes').val(1)
            if (!$('.list-product-attribute-items-wrap .product-attribute-set-item').length) {
                addAttributeSet()
            }
        } else {
            $('#is_added_attributes').val(0)
        }

        let toggleText = $this.data('toggle-text')
        $this.data('toggle-text', $this.text())
        $this.text(toggleText)
    })

    let handleChangeAttributeSet = () => {
        let $options = $('.list-product-attribute-items-wrap .product-attribute-set-item .product-select-attribute-item option')
        $.each($options, (index, el) => {
            let $el = $(el)
            let value = $el.prop('value')
            if (value !== $el.closest('select').val()) {
                if ($('.list-product-attribute-items-wrap .product-attribute-set-item .product-select-attribute-item[data-set-id=' + value + ']').length === 0) {
                    $el.prop('disabled', false)
                } else {
                    $el.prop('disabled', true)
                }
            }
        })

        let selectedItems = []
        $.each($('.product-select-attribute-item'), (index, el) => {
            if ($(el).val() !== '') {
                selectedItems.push(index)
            }
        })

        if (selectedItems.length) {
            $('.btn-trigger-add-attribute-to-simple-product').removeClass('hidden')
        } else {
            $('.btn-trigger-add-attribute-to-simple-product').addClass('hidden')
        }
    }

    $(document).on('change', '.product-select-attribute-item', event => {
        let $this = $(event.currentTarget)
        let $attrSetItem = $this.closest('.product-attribute-set-item')
        let selectedValue = $this.val()

        let $setSelect = $attrSetItem.find('.product-select-attribute-item')
        $setSelect.attr('data-set-id', $this.val())

        $attributeValue = $attrSetItem.find('.product-select-attribute-item-value')

        $attributeValue.prop('name', 'added_attributes[' + selectedValue + ']')
        let data = productAttributeSets.find((item) => item.id == selectedValue).attributes.map(item => {
            return { id: item.id, text: item.title }
        })
        $attributeValue.empty().select2({
            data,
            ajax: {},
            dataAdapter: CustomDataApdapterSelect2,
        })

        handleChangeAttributeSet()
    })

    let addAttributeSet = () => {
        let $attributeItemTemplate = $('#attribute_item_wrap_template')

        let id = 'product-attribute-set-' + (Math.random() + 1).toString(36).substring(7)
        let html = $attributeItemTemplate.html().replace('__id__', id)

        let selectedValue = null
        if ($('.list-product-attribute-items-wrap .product-attribute-set-item').length) {
            $.each($('.product-attribute-set-item .product-select-attribute-item option'), (index, el) => {
                let $el = $(el)
                let value = $el.prop('value')
                if (value !== $el.closest('select').val() && $el.prop('disabled') === false) {
                    selectedValue = value
                    return false
                }
            })
        }
        let $listDetailWrap = $('.list-product-attribute-items-wrap')

        $listDetailWrap.append(html)

        let $attributeSet = $('#' + id).find('.product-select-attribute-item')
        $attributeSet.select2({
            data: productAttributeSets.map(item => {
                return { id: item.id, text: item.title }
            }),
        })

        if (selectedValue) {
            $attributeSet.val(selectedValue).trigger('change')
        } else {
            $attributeSet.trigger('change')
        }

        if ($listDetailWrap.find('.product-attribute-set-item').length == productAttributeSets.length) {
            $('.btn-trigger-add-attribute-item').addClass('hidden')
        }
    }

    $(document).on('click', '.btn-trigger-add-attribute-item', event => {
        event.preventDefault()

        addAttributeSet()

        $('.product-set-item-delete-action').removeClass('hidden')

        handleChangeAttributeSet()
    })

    $(document).on('click', '.product-set-item-delete-action a', event => {
        event.preventDefault()
        $(event.currentTarget).closest('.product-attribute-set-item').remove()
        let totalAttributeSets = $('.list-product-attribute-items-wrap .product-attribute-set-item').length

        if (totalAttributeSets < 2) {
            $('.product-set-item-delete-action').addClass('hidden')
        } else if (totalAttributeSets < productAttributeSets.length) {
            $('.btn-trigger-add-attribute-item').removeClass('hidden')
        }

        handleChangeAttributeSet()
    })

    if (typeof RvMediaStandAlone != 'undefined') {
        new RvMediaStandAlone('.images-wrapper .btn-trigger-edit-product-image', {
            filter: 'image',
            view_in: 'all_media',
            onSelectFiles: (files, $el) => {
                let firstItem = _.first(files)

                let $currentBox = $el.closest('.product-image-item-handler').find('.image-box')
                let $currentBoxList = $el.closest('.list-gallery-media-images')

                $currentBox.find('.image-data').val(firstItem.url)
                $currentBox.find('.preview_image').attr('src', firstItem.thumb).show()

                _.forEach(files, (file, index) => {
                    if (!index) {
                        return
                    }
                    let template = $(document).find('#product_select_image_template').html()

                    let imageBox = template
                        .replace(/__name__/gi, $currentBox.find('.image-data').attr('name'))

                    let $template = $('<li class="product-image-item-handler">' + imageBox + '</li>')

                    $template.find('.image-data').val(file.url)
                    $template.find('.preview_image').attr('src', file.thumb).show()

                    $currentBoxList.append($template)
                })
            },
        })
    }

    $(document).on('click', '.btn-trigger-remove-product-image', event => {
        event.preventDefault()
        $(event.currentTarget).closest('.product-image-item-handler').remove()
        if ($('.list-gallery-media-images').find('.product-image-item-handler').length === 0) {
            $('.default-placeholder-product-image').removeClass('hidden')
        }
    })

    $(document).on('click', '.list-search-data .selectable-item', event => {
        event.preventDefault()
        let _self = $(event.currentTarget)
        let $input = _self.closest('.form-group').find('input[type=hidden]')

        let existedValues = $input.val().split(',')
        $.each(existedValues, (index, el) => {
            existedValues[index] = parseInt(el)
        })

        if ($.inArray(_self.data('id'), existedValues) < 0) {
            if ($input.val()) {
                $input.val($input.val() + ',' + _self.data('id'))
            } else {
                $input.val(_self.data('id'))
            }

            let template = $(document).find('#selected_product_list_template').html()

            let productItem = template
                .replace(/__name__/gi, _self.data('name'))
                .replace(/__id__/gi, _self.data('id'))
                .replace(/__url__/gi, _self.data('url'))
                .replace(/__image__/gi, _self.data('image'))
                .replace(/__attributes__/gi, _self.find('a span').text())
            _self.closest('.form-group').find('.list-selected-products').removeClass('hidden')
            _self.closest('.form-group').find('.list-selected-products table tbody').append(productItem)
        }
        _self.closest('.panel').addClass('hidden')
    })

    $(document).on('click', '.textbox-advancesearch', event => {
        let _self = $(event.currentTarget)
        let $formBody = _self.closest('.box-search-advance').find('.panel')
        $formBody.removeClass('hidden')
        $formBody.addClass('active')
        if ($formBody.find('.panel-body').length === 0) {
            Botble.blockUI({
                target: $formBody,
                iconOnly: true,
                overlayColor: 'none',
            })

            $.ajax({
                url: _self.data('target'),
                type: 'GET',
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        $formBody.html(res.data)
                        Botble.unblockUI($formBody)
                    }
                },
                error: data => {
                    Botble.handleError(data)
                    Botble.unblockUI($formBody)
                },
            })
        }
    })

    let ajaxRequest
    let hasAjaxSearchRequested = false
    $(document).on('keyup', '.textbox-advancesearch', event => {
        event.preventDefault()
        let _self = $(event.currentTarget)
        let $formBody = _self.closest('.box-search-advance').find('.panel')
        setTimeout(() => {
            Botble.blockUI({
                target: $formBody,
                iconOnly: true,
                overlayColor: 'none',
            })

            if (hasAjaxSearchRequested) {
                ajaxRequest.abort()
            }

            hasAjaxSearchRequested = true

            ajaxRequest = $.ajax({
                url: _self.data('target'),
                data: { keyword: _self.val() },
                type: 'GET',
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        $formBody.html(res.data)
                        Botble.unblockUI($formBody)
                    }
                    hasAjaxSearchRequested = false
                },
                error: data => {
                    if (data.statusText !== 'abort') {
                        Botble.handleError(data)
                        Botble.unblockUI($formBody)
                    }
                },
            })
        }, 500)
    })

    $(document).on('click', '.box-search-advance .page-link', event => {
        event.preventDefault()
        let $searchBox = $(event.currentTarget).closest('.box-search-advance').find('.textbox-advancesearch')
        if (!$searchBox.closest('.page-item').hasClass('disabled') && $searchBox.data('target')) {
            let $formBody = $searchBox.closest('.box-search-advance').find('.panel')
            Botble.blockUI({
                target: $formBody,
                iconOnly: true,
                overlayColor: 'none',
            })

            $.ajax({
                url: $(event.currentTarget).prop('href'),
                data: { keyword: $searchBox.val() },
                type: 'GET',
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        $formBody.html(res.data)
                        Botble.unblockUI($formBody)
                    }
                },
                error: data => {
                    Botble.handleError(data)
                    Botble.unblockUI($formBody)
                },
            })
        }
    })

    $(document).on('click', 'body', e => {
        let container = $('.box-search-advance')

        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.find('.panel').addClass('hidden')
        }
    })

    $(document).on('click', '.btn-trigger-remove-selected-product', event => {
        event.preventDefault()
        let $input = $(event.currentTarget).closest('.form-group').find('input[type=hidden]')

        let existedValues = $input.val().split(',')
        $.each(existedValues, (index, el) => {
            el = el.trim()
            if (!_.isEmpty(el)) {
                existedValues[index] = parseInt(el)
            }
        })
        let index = existedValues.indexOf($(event.currentTarget).data('id'))
        if (index > -1) {
            existedValues.splice(index, 1)
        }

        $input.val(existedValues.join(','))

        if ($(event.currentTarget).closest('tbody').find('tr').length < 2) {
            $(event.currentTarget).closest('.list-selected-products').addClass('hidden')
        }
        $(event.currentTarget).closest('tr').remove()
    })

    let loadRelationBoxes = () => {
        let $wrapBody = $('.wrap-relation-product')
        if ($wrapBody.length) {
            Botble.blockUI({
                target: $wrapBody,
                iconOnly: true,
                overlayColor: 'none',
            })

            $.ajax({
                url: $wrapBody.data('target'),
                type: 'GET',
                success: res => {
                    if (res.error) {
                        Botble.showError(res.message)
                    } else {
                        $wrapBody.html(res.data)
                        Botble.unblockUI($wrapBody)
                    }
                },
                error: data => {
                    Botble.handleError(data)
                    Botble.unblockUI($wrapBody)
                },
            })
        }
    }

    $(function() {
        loadRelationBoxes()
    })

    $(document).on('click', '.digital_attachments_btn', function(e) {
        e.preventDefault()
        const $this = $(e.currentTarget)
        const $box = $this.closest('.product-type-digital-management')
        const $inputFile = $box.find('input[type=file]').last()
        $inputFile.trigger('click')
    })

    $(document).on('change', 'input[name^=product_files_input]', function(e) {
        const $this = $(e.currentTarget)
        const file = $this[0].files[0]
        if (file) {
            const $box = $this.closest('.product-type-digital-management')
            let id = $this.data('id')
            let $template = $('#digital_attachment_template').html()
            $template = $template
                .replace(/__id__/gi, id)
                .replace(/__file_name__/gi, file.name)
                .replace(/__file_size__/gi, humanFileSize(file.size))

            let newId = Math.floor(Math.random() * 26) + Date.now()

            $box.find('table tbody').append($template)
            $box.find('.digital_attachments_input').append(`<input type="file" name="product_files_input[]" data-id="${newId}">`)
        }
    })

    $(document).on('change', 'input.digital-attachment-checkbox', function(e) {
        const $this = $(e.currentTarget)
        const $tr = $this.closest('tr')
        if ($this.is(':checked')) {
            $tr.removeClass('detach')
        } else {
            $tr.addClass('detach')
        }
    })

    $(document).on('click', '.remove-attachment-input', function(e) {
        const $this = $(e.currentTarget)
        const $tr = $this.closest('tr')
        let id = $tr.data('id')
        $('input[data-id=' + id + ']').remove()
        $tr.fadeOut(300, function() {
            $(this).remove()
        })
    })

    $(document).on('click', '.digital_attachments_external_btn', function(e) {
        e.preventDefault()
        const $this = $(e.currentTarget)
        const $box = $this.closest('.product-type-digital-management')
        let id = Math.floor(Math.random() * 26) + Date.now()
        let $template = $('#digital_attachment_external_template').html()
        $template = $template
            .replace(/__id__/gi, id)


        $box.find('table tbody').append($template)
    })

    /**
     * Format bytes as human-readable text.
     *
     * @param bytes Number of bytes.
     * @param si True to use metric (SI) units, aka powers of 1000. False to use
     *           binary (IEC), aka powers of 1024.
     * @param dp Number of decimal places to display.
     *
     * @return Formatted string.
     */
    function humanFileSize(bytes, si = true, dp = 2) {
        const thresh = si ? 1000 : 1024

        if (Math.abs(bytes) < thresh) {
            return bytes + ' B'
        }

        const units = si
            ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
            : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB']
        let u = -1
        const r = 10 ** dp

        do {
            bytes /= thresh
            ++u
        } while (Math.round(Math.abs(bytes) * r) / r >= thresh && u < units.length - 1)

        return Botble.numberFormat(bytes, dp, ',', '.') + ' ' + units[u]
    }

    $(document)
        .on('click', '.btn-trigger-duplicate-product', function (e) {
            $('#confirm-duplicate-product-button').data('url', $(e.currentTarget).data('url'))
            $('#duplicate-product-modal').modal('show')
        })
        .on('click', '#confirm-duplicate-product-button', function (e) {
            const button = $(e.currentTarget)

            $.ajax({
                url: button.data('url'),
                type: 'POST',
                beforeSend: () => {
                    button.prop('disabled', true)
                    button.addClass('button-loading')
                },
                success: ({ error, message, data }) => {
                    if (error) {
                        Botble.showError(message)
                        return
                    }

                    Botble.showSuccess(message)
                    $('#duplicate-product-modal').modal('hide')

                    setTimeout(() => window.location.href = data.next_url, 1000)
                },
                error: (error) => {
                    Botble.handleError(error)
                },
                complete: () => {
                    button.removeClass('button-loading')
                    button.prop('disabled', false)
                },
            })
        })
})
