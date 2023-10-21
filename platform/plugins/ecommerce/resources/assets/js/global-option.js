'use strict'
$(document).ready(function() {
    let jsOption = {
        currentType: 'N/A',
        init() {
            this.initFormFields($('.option-type').val())
            this.eventListeners()
            $('.option-sortable').sortable({
                stop: function() {
                    let idsInOrder = $('.option-sortable').sortable('toArray', { attribute: 'data-index' })
                    idsInOrder.map(function(id, index) {
                        $('.option-row[data-index="' + id + '"]').find('.option-order').val(index)
                    })
                },
            })
        },
        addNewRow() {
            $('.add-new-row').click(function() {
                let table = $(this).parent().find('table tbody')
                let tr = table.find('tr').last().clone()
                let labelName = 'options[' + table.find('tr').length + '][option_value]',
                    affectName = 'options[' + table.find('tr').length + '][affect_price]',
                    affectTypeName = 'options[' + table.find('tr').length + '][affect_type]'
                tr.find('.option-label').attr('name', labelName)
                tr.find('.affect_price').attr('name', affectName)
                tr.find('.affect_type').attr('name', affectTypeName)
                table.append(tr)
            })

            return this
        },
        removeRow() {
            $('.option-setting-tab').on('click', '.remove-row', function() {
                let table = $(this).parent().parent().parent()
                if (table.find('tr').length > 1) {
                    $(this).parent().parent().remove()
                } else {
                    return false
                }
            })
            return this
        },
        eventListeners() {
            this.onOptionChange()
            this.addNewRow().removeRow()
        },
        onOptionChange() {
            let self = this
            $('.option-type').change(function() {
                let value = $(this).val()
                this.currentType = value
                self.initFormFields(value)

            })
        },
        initFormFields(value) {
            this.currentType = value
            if (value !== 'N/A') {
                value = value.split('\\')
                let id = value[value.length - 1]
                if (id !== 'Field') {
                    id = 'multiple'
                }
                $('.empty, .option-setting-tab').hide()
                id = '#option-setting-' + id.toLowerCase()
                $(id).show()
            }
        },
    }
    jsOption.init()
})
