class ShippingManagement {
    init() {
        $(document).on('click', '.btn-confirm-delete-region-item-modal-trigger', event => {
            event.preventDefault()
            let $modal = $('#confirm-delete-region-item-modal')
            $modal.find('.region-item-label').text($(event.currentTarget).data('name'))
            $modal.find('#confirm-delete-region-item-button').data('id', $(event.currentTarget).data('id'))
            $modal.modal('show')
        })

        $(document).on('click', '#confirm-delete-region-item-button', event => {
            event.preventDefault()
            let _self = $(event.currentTarget)
            _self.addClass('button-loading')

            $.ajax({
                type: 'POST',
                url: $('div[data-delete-region-item-url]').data('delete-region-item-url'),
                data: {
                    _method: 'DELETE',
                    id: _self.data('id'),
                },
                success: res => {
                    if (!res.error) {
                        $('.wrap-table-shipping-' + _self.data('id')).remove()
                        Botble.showSuccess(res.message)
                    } else {
                        Botble.showError(res.message)
                    }
                    $('#confirm-delete-region-item-modal').modal('hide')
                },
                error: error => {
                    Botble.handleError(error)
                },
                complete: () => {
                    _self.removeClass('button-loading')
                },
            })
        })

        $(document).on('click', '.btn-confirm-delete-price-item-modal-trigger', event => {
            event.preventDefault()
            let $modal = $('#confirm-delete-price-item-modal')
            $modal.find('.region-price-item-label').text($(event.currentTarget).data('name'))
            $modal.find('#confirm-delete-price-item-button').data('id', $(event.currentTarget).data('id'))
            $modal.modal('show')
        })

        $(document).on('click', '#confirm-delete-price-item-button', event => {
            event.preventDefault()
            let _self = $(event.currentTarget)
            _self.addClass('button-loading')

            $.ajax({
                type: 'POST',
                url: $('div[data-delete-rule-item-url]').data('delete-rule-item-url'),
                data: {
                    _method: 'DELETE',
                    id: _self.data('id'),
                },
                success: res => {
                    if (!res.error) {
                        $('.box-table-shipping-item-' + _self.data('id')).remove()
                        if (res.data.count === 0) {
                            $('.wrap-table-shipping-' + res.data.shipping_id).remove()
                        }
                        Botble.showSuccess(res.message)
                    } else {
                        Botble.showError(res.message)
                    }
                    $('#confirm-delete-price-item-modal').modal('hide')
                },
                error: error => {
                    Botble.handleError(error)
                },
                complete: () => {
                    _self.removeClass('button-loading')
                },
            })
        })

        let saveRuleItem = ($this, $form, method, shippingId) => {

            $(document).find('.field-has-error').removeClass('field-has-error')
            let _self = $this
            _self.addClass('button-loading')

            let formData = []

            if (method !== 'POST') {
                formData._method = method
            }

            $.each($form.serializeArray(), (index, el) => {
                if (el.name === 'from' || el.name === 'to' || el.name === 'price') {
                    if (el.value) {
                        el.value = parseFloat(el.value.replace(',', '')).toFixed(2)
                    }
                }
                formData[el.name] = el.value
            })

            if (shippingId) {
                formData.shipping_id = shippingId
            }

            formData = $.extend({}, formData)

            $.ajax({
                type: 'POST',
                url: $form.prop('action'),
                data: formData,
                success: res => {
                    if (!res.error) {
                        Botble.showSuccess(res.message)
                        if (res?.data?.rule?.shipping_id && res?.data?.html) {
                            const $box = $('.wrap-table-shipping-' + res.data.rule.shipping_id + ' .pd-all-20.border-bottom')
                            const $item = $box.find('.box-table-shipping-item-' + res.data.rule.id)

                            if ($item.length) {
                                $item.replaceWith(res.data.html)
                            } else {
                                $box.append(res.data.html)
                            }
                            Botble.initResources()
                        }
                    } else {
                        Botble.showError(res.message)
                    }

                    if (shippingId) {
                        _self.closest('.modal').modal('hide')
                    }
                },
                error: error => {
                    Botble.handleError(error)
                },
                complete: () => {
                    _self.removeClass('button-loading')
                },
            })
        }

        $(document).on('click', '.btn-save-rule', event => {
            event.preventDefault()
            const $this = $(event.currentTarget)
            saveRuleItem($this, $this.closest('form'), 'PUT', null)
        })

        $(document).on('change', '.select-rule-type', event => {
            event.preventDefault()
            let _self = $(event.currentTarget)

            const $box = _self.closest('form')
            const $option = _self.find('option:selected')

            if ($option.data('show-from-to')) {
                $box.find('.rule-from-to-inputs').removeClass('d-none')
            } else {
                $box.find('.rule-from-to-inputs').addClass('d-none')
            }

            $box.find('.unit-item-label').text($option.data('unit'))
            $box.find('.rule-from-to-label').text($option.data('text'))
        })

        $(document).on('keyup', '.input-sync-item', event => {
            const $this = $(event.currentTarget)
            let number = $this.val()
            if (!number || isNaN(number)) {
                number = 0
            }
            $this.closest('.input-shipping-sync-wrapper').find($this.data('target')).text(Botble.numberFormat(parseFloat(number), 2))
        })

        $(document).on('keyup', '.input-sync-text-item', event => {
            const $this = $(event.currentTarget)
            $this.closest('.input-shipping-sync-wrapper').find($this.data('target')).text($this.val())
        })

        $(document).on('keyup', '.input-to-value-field', event => {
            const $this = $(event.currentTarget)
            const $parent = $this.closest('.input-shipping-sync-wrapper')
            if ($this.val()) {
                $parent.find('.rule-to-value-wrap').removeClass('hidden')
                $parent.find('.rule-to-value-missing').addClass('hidden')
            } else {
                $parent.find('.rule-to-value-wrap').addClass('hidden')
                $parent.find('.rule-to-value-missing').removeClass('hidden')
            }
        })

        $(document).on('click', '.btn-add-shipping-rule-trigger', event => {
            event.preventDefault()
            const $this = $(event.currentTarget)
            const $modal = $('#add-shipping-rule-item-modal')
            $('#add-shipping-rule-item-button').data('shipping-id', $this.data('shipping-id'))
            $modal.find('select[name=type] option[disabled]').prop('disabled', false)
            if (!$this.data('country')) {
                $modal.find('select[name=type] option[value=base_on_zip_code]').prop('disabled', true)
            }

            $modal.find('input[name=name]').val('')
            $modal.find('select[name=type]').val('').trigger('change')
            $modal.find('input[name=from]').val('0')
            $modal.find('input[name=to]').val('')
            $modal.find('input[name=price]').val('0')
            $modal.modal('show')
        })

        $(document).on('click', '.btn-shipping-rule-item-trigger', event => {
            event.preventDefault()
            const $this = $(event.currentTarget)
            const $modal = $('#form-shipping-rule-item-detail-modal')

            $modal.modal('show')

            $.ajax({
                type: 'GET',
                url: $this.data('url'),
                beforeSend: () => {
                    $modal.find('.modal-title strong').html('')
                    $modal.find('.modal-body').html(`<div class='w-100 text-center py-3'><div class='spinner-border' role='status'>
                    <span class='visually-hidden'>Loading...</span>
                  </div></div>`)
                },
                success: res => {
                    if (!res.error) {
                        $modal.find('.modal-body').html(res.data.html)
                        $modal.find('.modal-title strong').html(res.message)
                        Botble.initResources()
                    } else {
                        Botble.showError(res.message)
                    }
                },
                error: error => {
                    Botble.handleError(error)
                },
            })
        })

        $(document).on('click', '#save-shipping-rule-item-detail-button', event => {
            event.preventDefault()
            const $this = $(event.currentTarget)
            const $modal = $('#form-shipping-rule-item-detail-modal')
            const $form = $modal.find('form')

            $.ajax({
                type: $form.prop('method'),
                url: $form.prop('action'),
                data: $form.serialize(),
                beforeSend: () => {
                    $this.addClass('button-loading')
                },
                success: res => {
                    if (!res.error) {
                        const $table = $('.table-shipping-rule-' + res.data.shipping_rule_id)
                        if ($table.find('.shipping-rule-item-' + res.data.id).length) {
                            $table.find('.shipping-rule-item-' + res.data.id).replaceWith(res.data.html)
                        } else {
                            $table.prepend(res.data.html)
                        }
                        $modal.modal('hide')
                        Botble.showSuccess(res.message)
                    } else {
                        Botble.showError(res.message)
                    }
                },
                error: error => {
                    Botble.handleError(error)
                },
                complete: () => {
                    $this.removeClass('button-loading')
                },
            })
        })

        $(document).on('click', '.btn-confirm-delete-rule-item-modal-trigger', event => {
            event.preventDefault()
            let $modal = $('#confirm-delete-shipping-rule-item-modal')
            $modal.find('.item-label').text($(event.currentTarget).data('name'))
            $modal.find('#confirm-delete-shipping-rule-item-button').data('url', $(event.currentTarget).data('section'))
            $modal.modal('show')
        })

        $(document).on('click', '#confirm-delete-shipping-rule-item-button', event => {
            event.preventDefault()
            let _self = $(event.currentTarget)
            _self.addClass('button-loading')

            $.ajax({
                type: 'POST',
                url: _self.data('url'),
                data: {
                    _method: 'DELETE',
                },
                success: res => {
                    if (!res.error) {
                        const $table = $('.table-shipping-rule-' + res.data.shipping_rule_id)
                        if ($table.find('.shipping-rule-item-' + res.data.id).length) {
                            $table.find('.shipping-rule-item-' + res.data.id).fadeOut(500, function() {
                                $(this).remove()
                            })
                        }
                        Botble.showSuccess(res.message)
                    } else {
                        Botble.showError(res.message)
                    }
                    $('#confirm-delete-shipping-rule-item-modal').modal('hide')
                },
                error: error => {
                    Botble.handleError(error)
                },
                complete: () => {
                    _self.removeClass('button-loading')
                },
            })
        })

        $(document).find('.select-country-search').select2({
            width: '100%',
            dropdownParent: $('#select-country-modal'),
        })

        $(document).on('click', '.btn-select-country', event => {
            event.preventDefault()
            $('#select-country-modal').modal('show')
        })

        $(document).on('click', '#add-shipping-region-button', event => {
            event.preventDefault()
            let _self = $(event.currentTarget)
            _self.addClass('button-loading')

            let $form = _self.closest('.modal-content').find('form')

            $.ajax({
                type: 'POST',
                url: $form.prop('action'),
                data: $form.serialize(),
                success: res => {
                    if (!res.error) {
                        Botble.showSuccess(res.message)
                        $('.wrapper-content').load(window.location.href + ' .wrapper-content > *')
                    } else {
                        Botble.showError(res.message)
                    }
                    _self.removeClass('button-loading')
                    $('#select-country-modal').modal('hide')
                },
                error: error => {
                    Botble.handleError(error)
                    _self.removeClass('button-loading')
                },
            })
        })

        $(document).on('click', '#add-shipping-rule-item-button', event => {
            event.preventDefault()
            saveRuleItem($(event.currentTarget), $(event.currentTarget).closest('.modal-content').find('form'), 'POST', $(event.currentTarget).data('shipping-id'))
        })

        $(document).on('keyup', '.base-price-rule-item', event => {
            let _self = $(event.currentTarget)
            let basePrice = _self.val()

            if (!basePrice || isNaN(basePrice)) {
                basePrice = 0
            }

            $.each($(document).find('.support-shipping .rule-adjustment-price-item'), (index, item) => {
                let adjustmentPrice = $(item).closest('tr').find('.shipping-price-district').val()
                if (!adjustmentPrice || isNaN(adjustmentPrice)) {
                    adjustmentPrice = 0
                }
                $(item).text(Botble.numberFormat(parseFloat(basePrice) + parseFloat(adjustmentPrice)), 2)
            })
        })

        $(document).on('change', 'select[name=shipping_rule_id].shipping-rule-id', function(e) {
            e.preventDefault()
            const $this = $(e.currentTarget)
            const $form = $this.closest('form')
            let $country = $form.find('select[data-type="country"]')
            const val = $this.find('option:selected').data('country')
            if ($country.length) {
                if ($country.val() != val) {
                    $country.val(val).trigger('change')
                }
            } else {
                $country = $form.find('input[name="country"]')
                if ($country.length && $country.val() != val) {
                    $country.val(val)
                }
            }
        })

        $(document).on('click', '.table-shipping-rule-items .shipping-rule-load-items', event => {
            event.preventDefault()

            let $this = $(event.currentTarget)
            const $table = $this.closest('.table-shipping-rule-items')
            loadRuleItems($this.attr('href'), $table, $this)

        })

        $(document).on('click', '.table-shipping-rule-items a.page-link', event => {
            event.preventDefault()

            let $this = $(event.currentTarget)
            const $table = $this.closest('.table-shipping-rule-items')
            loadRuleItems($this.attr('href'), $table, $this)
        })

        $(document).on('change', '.table-shipping-rule-items .number-record .numb', e => {
            e.preventDefault()
            const $this = $(e.currentTarget)
            let per_page = $this.val()

            if (!isNaN(per_page) && per_page > 0) {
                const $table = $this.closest('.table-shipping-rule-items')
                const $th = $table.find('thead tr th[data-column][data-dir]')
                let data = { per_page }

                if ($th.length) {
                    data.order_by = $th.data('column')
                    data.order_dir = $th.data('dir') || 'DESC'
                }
                loadRuleItems($table.data('url'), $table, $this, data)
            } else {
                $this.val($this.attr('min') || 12).trigger('change')
            }
        })

        $(document).on('click', '.table-shipping-rule-items thead tr th[data-column]', e => {
            e.preventDefault()
            const $this = $(e.currentTarget)
            let order_by = $this.data('column')
            let order_dir = $this.data('dir') || 'ASC'
            order_dir = order_dir == 'ASC' ? 'DESC' : 'ASC'

            const $table = $this.closest('.table-shipping-rule-items')

            const $numb = $table.find('.number-record .numb')
            let per_page = $numb.val()

            loadRuleItems($table.data('url'), $table, $this, { order_by, order_dir, per_page })
        })

        function loadRuleItems(url, $table, $button, data = {}) {
            $.ajax({
                type: 'GET',
                url: url,
                data: data,
                beforeSend: () => {
                    $button && $button.addClass('button-loading')
                    $table.addClass('table-loading')
                },
                success: res => {
                    if (!res.error) {
                        $table.replaceWith(res.data.html)
                    } else {
                        Botble.showError(res.message)
                    }
                },
                error: error => {
                    Botble.handleError(error)
                },
                complete: () => {
                    $button && $button.removeClass('button-loading')
                },
            })
        }
    }
}

$(() => {
    new ShippingManagement().init()
})
