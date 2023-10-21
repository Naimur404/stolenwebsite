$(document).ready(() => {
    $(document).on('click', '#is_change_password', event => {
        if ($(event.currentTarget).is(':checked')) {
            $('input[type=password]').closest('.form-group').removeClass('hidden').fadeIn()
        } else {
            $('input[type=password]').closest('.form-group').addClass('hidden').fadeOut()
        }
    })

    $(document).on('click', '.verify-customer-email-button', event => {
        event.preventDefault()
        $('#confirm-verify-customer-email-button').data('action', $(event.currentTarget).prop('href'))
        $('#verify-customer-email-modal').modal('show')
    })

    $(document).on('click', '#confirm-verify-customer-email-button', event => {
        event.preventDefault()
        let _self = $(event.currentTarget)

        _self.addClass('button-loading')

        $.ajax({
            type: 'POST',
            cache: false,
            url: _self.data('action'),
            success: res => {
                if (!res.error) {
                    Botble.showSuccess(res.message)
                    setTimeout(() => {
                        window.location.reload()
                    }, 2000)
                } else {
                    Botble.showError(res.message)
                }
                _self.removeClass('button-loading')
                _self.closest('.modal').modal('hide')
            },
            error: res => {
                Botble.handleError(res)
                _self.removeClass('button-loading')
            },
        })
    })
})
