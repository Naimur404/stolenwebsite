$(() => {
    if ($.rating) {
        $('#rating').rating({ 'size': 'xs' })
    }

    function handleError(data) {
        let messages = ''
        if (typeof (data.errors) !== 'undefined' && !Array.isArray(data.errors)) {
            messages = handleValidationError(data.errors)
        } else {
            if (typeof (data.responseJSON) !== 'undefined') {
                if (typeof (data.responseJSON.errors) !== 'undefined') {
                    if (data.status === 422) {
                        messages = handleValidationError(data.responseJSON.errors)
                    }
                } else if (typeof (data.responseJSON.message) !== 'undefined') {
                    messages = data.responseJSON.message
                } else {
                    $.each(data.responseJSON, (index, el) => {
                        $.each(el, (key, item) => {
                            messages += item + '<br />'
                        })
                    })
                }
            } else {
                messages = data.statusText
            }
        }

        return messages
    }

    function handleValidationError(errors) {
        let message = ''
        $.each(errors, (index, item) => {
            message += item + '<br />'
        })
        return message
    }

    function submitReviewProduct() {
        let imagesReviewBuffer = []
        let setImagesFormReview = function(input) {
            const dT = new ClipboardEvent('').clipboardData || // Firefox < 62 workaround exploiting https://bugzilla.mozilla.org/show_bug.cgi?id=1422655
                new DataTransfer() // specs compliant (as of March 2018 only Chrome)
            for (let file of imagesReviewBuffer) {
                dT.items.add(file)
            }
            input.files = dT.files
            loadPreviewImage(input)
        }

        let loadPreviewImage = function(input) {
            let $uploadText = $('.ecommerce-image-upload__text')
            const maxFiles = $(input).data('max-files')
            let filesAmount = input.files.length

            if (maxFiles) {
                if (filesAmount >= maxFiles) {
                    $uploadText.closest('.ecommerce-image-upload__uploader-container').addClass('d-none')
                } else {
                    $uploadText.closest('.ecommerce-image-upload__uploader-container').removeClass('d-none')
                }
                $uploadText.text(filesAmount + '/' + maxFiles)
            } else {
                $uploadText.text(filesAmount)
            }
            const viewerList = $('.ecommerce-image-viewer__list')
            const $template = $('#ecommerce-review-image-template').html()

            viewerList.addClass('is-loading')
            viewerList.find('.ecommerce-image-viewer__item').remove()

            if (filesAmount) {
                for (let i = filesAmount - 1; i >= 0; i--) {
                    viewerList.prepend($template.replace('__id__', i))
                }
                for (let j = filesAmount - 1; j >= 0; j--) {
                    let reader = new FileReader()
                    reader.onload = function(event) {
                        viewerList
                            .find('.ecommerce-image-viewer__item[data-id=' + j + ']')
                            .find('img')
                            .attr('src', event.target.result)
                    }
                    reader.readAsDataURL(input.files[j])
                }
            }
            viewerList.removeClass('is-loading')
        }

        $(document).on('change', '.ecommerce-form-review-product input[type=file]', function(event) {
            event.preventDefault()
            let input = this
            let $input = $(input)
            let maxSize = $input.data('max-size')
            Object.keys(input.files).map(function(i) {
                if (maxSize && (input.files[i].size / 1024) > maxSize) {
                    let message = $input.data('max-size-message')
                        .replace('__attribute__', input.files[i].name)
                        .replace('__max__', maxSize)
                    MartApp.showError(message)
                } else {
                    imagesReviewBuffer.push(input.files[i])
                }
            })

            let filesAmount = imagesReviewBuffer.length
            const maxFiles = $input.data('max-files')
            if (maxFiles && filesAmount > maxFiles) {
                imagesReviewBuffer.splice(filesAmount - maxFiles - 1, filesAmount - maxFiles)
            }

            setImagesFormReview(input)
        })

        $(document).on('click', '.ecommerce-form-review-product .ecommerce-image-viewer__icon-remove', function(event) {
            event.preventDefault()
            const $this = $(event.currentTarget)
            let id = $this.closest('.ecommerce-image-viewer__item').data('id')
            imagesReviewBuffer.splice(id, 1)

            let input = $('.ecommerce-form-review-product input[type=file]')[0]
            setImagesFormReview(input)
        })

        $(document).on('submit', '.ecommerce-form-review-product', function(e) {
            e.preventDefault()
            e.stopPropagation()
            const $this = $(e.currentTarget)
            const $button = $this.find('button[type=submit]')

            const productId = $this.find('input[name=product_id]').val()

            $.ajax({
                type: 'POST',
                cache: false,
                url: $this.prop('action'),
                data: new FormData($this[0]),
                contentType: false,
                processData: false,
                beforeSend: () => {
                    $button.prop('disabled', true).addClass('loading')
                    $this.find('.alert-message').removeClass('alert-success').addClass('d-none alert-warning')
                },
                success: res => {
                    if (!res.error) {
                        $this.find('textarea').val('')

                        const $item = $('.ecommerce-product-item[data-id=' + productId + ']')
                        $item.find('.ecommerce-product-star').addClass('text-primary h5').html(res.message)
                        if ($('#product-review-modal').length) {
                            $('#product-review-modal').modal('hide')
                        } else {
                            $this.find('.alert-message')
                                .removeClass('alert-warning d-none')
                                .addClass('alert-success')
                                .html(res.message)
                        }
                    } else {
                        $this.find('.alert-message').html(res.message).removeClass('d-none')
                    }
                },
                error: res => {
                    let messages = handleError(res)
                    $this.find('.alert-message').html(messages).removeClass('d-none')
                },
                complete: () => {
                    $button.prop('disabled', false).removeClass('loading')
                },
            })
        })

        $(document).on('click', '.ecommerce-product-star .ecommerce-icon', function(e) {
            const $this = $(e.currentTarget)
            const $product = $this.closest('.ecommerce-product-item')

            const $modal = $('#product-review-modal')
            const $form = $modal.find('form')

            $modal.find('.ecommerce-product-image').attr('src', $product.find('.ecommerce-product-image').attr('src'))
            $modal.find('.ecommerce-product-name').text($product.find('.ecommerce-product-name').text())
            $form.find('input[name=star][value=' + $this.data('star') + ']').prop('checked', true).trigger('change')
            $form.find('input[name=product_id]').val($product.data('id'))

            $modal.modal('show')
        })

        $(document).on('hidden.bs.modal', '#product-review-modal', function(e) {
            const $this = $(e.currentTarget)
            $this.find('.ecommerce-produt-image').attr('src', '')
            $this.find('.ecommerce-produt-name').text('')
            $this.find('input[name=product_id]').val('')
        })
    }

    submitReviewProduct()
})
