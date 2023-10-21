'use strict'

$(document).ready(function() {
    $(document).on('click', '.btn-payout-button', event => {
        event.preventDefault()
        event.stopPropagation()

        const $this = $(event.currentTarget)

        $this.addClass('button-loading')

        $.ajax({
            type: 'POST',
            url: $this.prop('href'),
            success: res => {
                if (!res.error) {
                    Botble.showSuccess(res.message)

                    $this.closest('.widget.meta-boxes').remove()

                    window.location.reload()
                } else {
                    Botble.showError(res.message)
                }

                $this.removeClass('button-loading')
            },
            error: res => {
                $this.removeClass('button-loading')
                Botble.handleError(res)
            },
        })
    })

    const loadPayPalPayoutInfo = () => {
        const $transactionDetail = $('#payout-transaction-detail')

        $.ajax({
            type: 'GET',
            url: $transactionDetail.data('url'),
            success: res => {
                if (!res.error) {
                    $transactionDetail.html(res.data.html)
                } else {
                    $transactionDetail.html('')
                }
            },
            error: res => {
                console.log(res)
            },
        })
    }

    loadPayPalPayoutInfo()
})
