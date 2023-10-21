const toggleReviewStatus = (url, button) => {
    $.ajax({
        url: url,
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

            $('#main').load(window.location.href + ' #main > *')
        },
        error: (error) => {
            Botble.handleError(error)
        },
        complete: () => {
            button.removeClass('button-loading')
            button.prop('disabled', false)
        }
    })
}

$(document)
    .on('click', '.btn-trigger-delete-review', function (e) {
        $('#confirm-delete-review-button').data('target', $(e.currentTarget).data('target'))
        $('#delete-review-modal').modal('show')
    })
    .on('click', '#confirm-delete-review-button', function (e) {
        const button = $(e.currentTarget)

        $.ajax({
            url: button.data('target'),
            type: 'POST',
            data: {
                _method: 'DELETE',
            },
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
                $('#delete-review-modal').modal('hide')

                setTimeout(() => window.location.href = data.next_url, 2000)
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
    .on('click', '.btn-trigger-unpublish-review', function (e) {
        const button = $(e.currentTarget)

        toggleReviewStatus(route('reviews.unpublish', button.data('id')), button)
    })
    .on('click', '.btn-trigger-publish-review', function (e) {
        const button = $(e.currentTarget)

        toggleReviewStatus(route('reviews.publish', button.data('id')), button)
    })
