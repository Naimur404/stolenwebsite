class OrderIncompleteManagement {
    init() {
        $(document).on('click', '.btn-update-order', event => {
            event.preventDefault()
            let _self = $(event.currentTarget)

            _self.addClass('button-loading')

            $.ajax({
                type: 'POST',
                cache: false,
                url: _self.closest('form').prop('action'),
                data: _self.closest('form').serialize(),
                success: res => {
                    if (!res.error) {
                        Botble.showSuccess(res.message)
                    } else {
                        Botble.showError(res.message)
                    }
                    _self.removeClass('button-loading')
                },
                error: res => {
                    Botble.handleError(res)
                    _self.removeClass('button-loading')
                },
            })
        })

        $(document).on('click', '.btn-trigger-send-order-recover-modal', event => {
            event.preventDefault()
            $('#confirm-send-recover-email-button').data('action', $(event.currentTarget).data('action'))
            $('#send-order-recover-email-modal').modal('show')
        })

        $(document).on('click', '.btn-mark-order-as-completed-modal', event => {
            event.preventDefault()

            $('#confirm-mark-as-completed-button').data('action', $(event.currentTarget).data('action'))
            $('#mark-order-as-completed-modal').modal('show')
        })

        $(document).on('click', '#confirm-send-recover-email-button', event => {
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
                    } else {
                        Botble.showError(res.message)
                    }
                    _self.removeClass('button-loading')
                    $('#send-order-recover-email-modal').modal('hide')
                },
                error: res => {
                    Botble.handleError(res)
                    _self.removeClass('button-loading')
                },
            })
        })

        $(document).on('click', '#confirm-mark-as-completed-button', event => {
            event.preventDefault()

            const button = $(event.currentTarget)

            $.ajax({
                type: 'POST',
                cache: false,
                url: button.data('action'),
                beforeSend: () => {
                    button.addClass('button-loading')
                },
                success: ({ error, message, data }) => {
                    if (error) {
                        Botble.showError(message)
                        return
                    }

                    $('#mark-order-as-completed-modal').modal('hide')
                    Botble.showSuccess(message)

                    if (data.next_url) {
                        setTimeout(() => window.location.href = data.next_url, 2000)
                    }
                },
                error: (error) => {
                    Botble.handleError(error)
                },
                completed: () => {
                    button.removeClass('button-loading')
                }
            })
        })
    }
}

$(document).ready(() => {
    new OrderIncompleteManagement().init()
})
