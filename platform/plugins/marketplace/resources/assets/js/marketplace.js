(function ($) {
    'use strict';

    $('.menu-item.has-submenu .menu-link').on('click', function (event) {
        event.preventDefault();
        if ($(this).next('.submenu').is(':hidden')) {
            $(this).parent('.has-submenu').siblings().find('.submenu').slideUp(200);
        }

        $(this).next('.submenu').slideToggle(200);
    });

    $('[data-trigger]').on('click', function (event) {
        event.preventDefault();
        event.stopPropagation();

        let target = $(this).attr('data-trigger');

        $(target).toggleClass('show');
        $('body').toggleClass('offcanvas-active');
        $('.screen-overlay').toggleClass('show');
    });

    $('.screen-overlay, .btn-close').click(function () {
        $('.screen-overlay').removeClass('show');
        $('.mobile-offcanvas, .show').removeClass('show');
        $('body').removeClass('offcanvas-active');
    });

    $('.btn-aside-minimize').on('click', function () {
        if (window.innerWidth < 768) {
            $('body').removeClass('aside-mini');
            $('.screen-overlay').removeClass('show');
            $('.navbar-aside').removeClass('show');
            $('body').removeClass('offcanvas-active');
        } else {
            $('body').toggleClass('aside-mini');
        }
    });

    if ($('.select-nice').length) {
        $('.select-nice').select2();
    }

    function readURL(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $(input).closest('.image-box').find('.preview_image').prop('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    $(function () {

        $('#shop-url')
            .on('keyup', function () {
                let displayURL = $(this).closest('.form-group').find('span small');
                displayURL.html(displayURL.data('base-url') + '/<strong>' + $(this).val().toLowerCase() + '</strong>');
            })
            .on('change', function () {
                $('.shop-url-wrapper').addClass('content-loading');
                $(this).closest('form').find('button[type=submit]').addClass('btn-disabled').prop('disabled', true);

                $.ajax({
                    url: $(this).data('url'),
                    type: 'POST',
                    data: {
                        url: $(this).val(),
                        reference_id: $('input[name=reference_id]').val()
                    },
                    success: res => {
                        $('.shop-url-wrapper').removeClass('content-loading');
                        if (res.error) {
                            $('.shop-url-status').removeClass('text-success').addClass('text-danger').text(res.message);

                        } else {
                            $('.shop-url-status').removeClass('text-danger').addClass('text-success').text(res.message);
                            $(this).closest('form').find('button[type=submit]').prop('disabled', false).removeClass('btn-disabled');
                        }
                    },
                    error: () => {
                        $('.shop-url-wrapper').removeClass('content-loading');
                    }
                });
            });

        $('.custom-select-image').on('click', function (event) {
            event.preventDefault();
            $(this).closest('.image-box').find('.image_input').trigger('click');
        });

        $('.image_input').on('change', function () {
            readURL(this);
        });

        $(document).on('click', '.btn_remove_image', event => {
            event.preventDefault();
            let $img = $(event.currentTarget).closest('.image-box').find('.preview-image-wrapper .preview_image');
            $img.attr('src', $img.data('default-image'));
            $(event.currentTarget).closest('.image-box').find('.image-data').val('');
        });

        if (window.noticeMessages && window.noticeMessages.length) {
            noticeMessages.map(x => {
                Botble.showNotice(x.type, x.message, '');
            });
        }
    });
})(jQuery);
