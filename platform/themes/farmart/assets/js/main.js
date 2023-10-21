'use strict'

let MartApp = MartApp || {}
window.MartApp = MartApp
MartApp.$iconChevronLeft =
    '<span class="slick-prev-arrow svg-icon"><svg><use href="#svg-icon-chevron-left" xlink:href="#svg-icon-chevron-left"></use></svg></span>'
MartApp.$iconChevronRight =
    '<span class="slick-next-arrow svg-icon"><svg><use href="#svg-icon-chevron-right" xlink:href="#svg-icon-chevron-right"></use></svg></span>'

window._scrollBar = new ScrollBarHelper()

MartApp.isRTL = $('body').prop('dir') === 'rtl';

(function($) {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    })

    function basicEvents() {
        $('.form--quick-search .form-group--icon').show()
        let $categoryLabel = $('.product-category-label .text')
        $(document).on('change', '.product-category-select', function() {
            $categoryLabel.text($.trim($(this).find('option:selected').text()))
        })

        $categoryLabel.text(
            $.trim($('.product-category-select option:selected').text()),
        )

        $(document).ready(function() {
            $('.preloader').addClass('fade-in')
        })
    }

    function subMenuToggle() {
        $('.menu-item-has-children > a > .sub-toggle').on(
            'click',
            function(e) {
                e.preventDefault()
                const $this = $(this)
                const $parent = $this.closest('.menu-item-has-children')
                $parent.toggleClass('active')
            },
        )

        $('.mega-menu__column > a > .sub-toggle').on(
            'click',
            function(e) {
                e.preventDefault()
                const $this = $(this)
                const $parent = $this.closest('.mega-menu__column')
                $parent.toggleClass('active')
            },
        )
    }

    function siteToggleAction() {
        $('.toggle--sidebar').on('click', function(e) {
            e.preventDefault()

            let url = $(this).attr('href')

            $(this).toggleClass('active')
            $(this).siblings('a').removeClass('active')

            $(url).toggleClass('active')

            $(url).siblings('.panel--sidebar').removeClass('active')
            _scrollBar.hide()
        })

        $(document).on('click', '.close-toggle--sidebar', function(e) {
            e.preventDefault()
            let $panel

            if ($(this).data('toggle-closest')) {
                $panel = $(this).closest($(this).data('toggle-closest'))
            }

            if (!$panel || !$panel.length) {
                $panel = $(this).closest('.panel--sidebar')
            }

            $panel.removeClass('active')
            _scrollBar.reset()
        })

        $('body').on('click', function(e) {
            if ($(e.target).siblings('.panel--sidebar').hasClass('active')) {
                $('.panel--sidebar').removeClass('active')
                _scrollBar.reset()
            }
        })
    }

    $(function() {
        basicEvents()
        subMenuToggle()
        siteToggleAction()
    })

    MartApp.init = function() {
        MartApp.$body = $(document.body)

        MartApp.formSearch = '#products-filter-form'
        MartApp.$formSearch = $(document).find(MartApp.formSearch)
        MartApp.productListing = '.products-listing'
        MartApp.$productListing = $(MartApp.productListing)

        this.lazyLoad(null, true)
        this.productQuickView()
        this.slickSlides()
        this.productQuantity()
        this.addProductToWishlist()
        this.addProductToCompare()
        this.addProductToCart()
        this.applyCouponCode()
        this.productGallery()
        this.lightBox()
        this.handleTabBootstrap()
        this.toggleViewProducts()
        this.filterSlider()
        this.toolbarOrderingProducts()
        this.productsFilter()
        this.searchProducts()
        this.ajaxUpdateCart()
        this.removeCartItem()
        this.removeWishlistItem()
        this.removeCompareItem()
        this.submitReviewProduct()
        this.vendorRegisterForm()
        this.customerDashboard()
        this.newsletterForm()
        this.contactSellerForm()
        this.stickyAddToCart()
        this.backToTop()
        this.stickyHeader()
        this.recentlyViewedProducts()
        this.reviewList()

        MartApp.$body.on(
            'click',
            '.catalog-sidebar .backdrop, #cart-mobile .backdrop',
            function(e) {
                e.preventDefault()
                $(this).parent().removeClass('active')
                _scrollBar.reset()
            },
        )

        MartApp.$body.on('click', '.sidebar-filter-mobile', function(e) {
            e.preventDefault()
            MartApp.toggleSidebarFilterProducts('open', $(e.currentTarget).data('toggle'))
        })

        MartApp.$body.on('submit', '.products-filter-form-vendor', function(e) {
            if (MartApp.$formSearch.length) {
                MartApp.$formSearch.trigger('submit')
                return false
            }
            return true
        })
    }

    MartApp.toggleSidebarFilterProducts = function(status = 'close', target = 'product-categories-primary-sidebar') {
        const $el = $('[data-toggle-target="' + target + '"]')
        if (status === 'close') {
            $el.removeClass('active')
            _scrollBar.reset()
        } else {
            $el.addClass('active')
            _scrollBar.hide()
        }
    }

    MartApp.productQuickView = function() {
        const $modal = $('#product-quick-view-modal')

        MartApp.$body.on(
            'click',
            '.product-quick-view-button .quick-view',
            function(e) {
                e.preventDefault()
                const _self = $(e.currentTarget)
                _self.addClass('loading')
                $modal.removeClass('loaded').addClass('loading')
                $modal.modal('show')
                $modal.find('.product-modal-content').html('')
                $.ajax({
                    url: _self.data('url'),
                    type: 'GET',
                    success: (res) => {
                        if (!res.error) {
                            $modal
                                .find('.product-modal-content')
                                .html(res.data)
                            MartApp.productGallery(true, $modal.find('.product-modal-content .product-gallery'))
                            MartApp.lightBox()
                            MartApp.lazyLoad($modal[0])
                        }
                    },
                    error: () => {
                    },
                    complete: () => {
                        $modal.addClass('loaded').removeClass('loading')
                        _self.removeClass('loading')
                    },
                })
            },
        )
    }

    MartApp.productGallery = function(destroy, $gallery) {
        if (!$gallery || !$gallery.length) {
            $gallery = $('.product-gallery')
        }

        if ($gallery.length) {
            const first = $gallery.find('.product-gallery__wrapper')
            const second = $gallery.find('.product-gallery__variants')
            if (destroy) {
                if (first.length && first.hasClass('slick-initialized')) {
                    first.slick('unslick')
                }

                if (second.length && second.hasClass('slick-initialized')) {
                    second.slick('unslick')
                }
            }

            first.not('.slick-initialized').slick({
                rtl: MartApp.isRTL,
                slidesToShow: 1,
                slidesToScroll: 1,
                infinite: false,
                asNavFor: second,
                dots: false,
                prevArrow: MartApp.$iconChevronLeft,
                nextArrow: MartApp.$iconChevronRight,
                lazyLoad: 'ondemand',
            })

            second.not('.slick-initialized').slick({
                rtl: MartApp.isRTL,
                slidesToShow: 8,
                slidesToScroll: 1,
                infinite: false,
                focusOnSelect: true,
                asNavFor: first,
                vertical: true,
                prevArrow: '<span class="slick-prev-arrow svg-icon"><svg><use href="#svg-icon-arrow-up" xlink:href="#svg-icon-arrow-up"></use></svg></span>',
                nextArrow: '<span class="slick-next-arrow svg-icon"><svg><use href="#svg-icon-chevron-down" xlink:href="#svg-icon-chevron-down"></use></svg></span>',
                responsive: [
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 6,
                            vertical: false,
                        },
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 3,
                            vertical: false,
                        },
                    },
                ],
            })
        }
    }

    MartApp.lightBox = function() {
        $('.product-gallery--with-images').lightGallery({
            selector: '.item a',
            thumbnail: true,
            share: false,
            fullScreen: false,
            autoplay: false,
            autoplayControls: false,
            actualSize: false,
        })

        let $galleries = $('.review-images-total.review-images')
        if ($galleries.length) {
            $galleries.map((index, value) => {
                if (!$(value).data('lightGallery')) {
                    $(value).lightGallery({
                        selector: 'a',
                        thumbnail: true,
                        share: false,
                        fullScreen: false,
                        autoplay: false,
                        autoplayControls: false,
                        actualSize: false,
                    })
                }
            })
        }
    }

    MartApp.slickSlide = function(el) {
        const $el = $(el)
        if ($el.length && $el.not('.slick-initialized')) {
            let slickOptions = $el.data('slick') || {}
            if (slickOptions.appendArrows) {
                slickOptions.appendArrows = $el
                    .parent()
                    .find(slickOptions.appendArrows)
            }
            slickOptions = Object.assign(slickOptions, {
                rtl: MartApp.isRTL,
                prevArrow: MartApp.$iconChevronLeft,
                nextArrow: MartApp.$iconChevronRight,
            })
            $el.slick(slickOptions)
        }
    }

    MartApp.slickSlides = function() {
        $('.slick-slides-carousel')
            .not('.slick-initialized')
            .map(function(i, e) {
                MartApp.slickSlide(e)
            })
    }

    MartApp.lazyLoad = function(container, init = false) {
        if (init) {
            MartApp.lazyLoadInstance = new LazyLoad({
                elements_selector: '.lazyload',
                callback_error: img => {
                    img.setAttribute('src', siteConfig.img_placeholder)
                },
            })
        } else {
            new LazyLoad({
                container: container,
                elements_selector: '.lazyload',
                callback_error: img => {
                    img.setAttribute('src', siteConfig.img_placeholder)
                },
            })
        }
    }

    MartApp.productQuantity = function() {
        MartApp.$body.on(
            'click',
            '.quantity .increase, .quantity .decrease',
            function(e) {
                e.preventDefault()
                let $this = $(this),
                    $wrapperBtn = $this.closest('.product-button'),
                    $btn = $wrapperBtn.find('.quantity_button'),
                    $price = $this
                        .closest('.quantity')
                        .siblings('.box-price')
                        .find('.price-current'),
                    $priceCurrent = $price.html(),
                    $qty = $this.siblings('.qty'),
                    step = parseInt($qty.attr('step'), 10),
                    current = parseInt($qty.val(), 10),
                    min = parseInt($qty.attr('min'), 10),
                    max = parseInt($qty.attr('max'), 10)
                min = min || 1
                max = max || current + 1
                if ($this.hasClass('decrease') && current > min) {
                    $qty.val(current - step)
                    $qty.trigger('change')
                    let numQuantity = +$btn.attr('data-quantity')
                    numQuantity = numQuantity - 1
                    $btn.attr('data-quantity', numQuantity)
                    let $total2 = (
                        $priceCurrent * 1 -
                        $priceCurrent / current
                    ).toFixed(2)
                    $price.html($total2)
                }
                if ($this.hasClass('increase') && current < max) {
                    $qty.val(current + step)
                    $qty.trigger('change')
                    let numQuantity = +$btn.attr('data-quantity')
                    numQuantity = numQuantity + 1
                    $btn.attr('data-quantity', numQuantity)
                    let $total = (
                        $priceCurrent * 1 +
                        $priceCurrent / current
                    ).toFixed(2)
                    $price.html($total)
                }

                MartApp.processUpdateCart($this)
            },
        )
        MartApp.$body.on('keyup', '.quantity .qty', function(e) {
            e.preventDefault()
            let $this = $(this),
                $wrapperBtn = $this.closest('.product-button'),
                $btn = $wrapperBtn.find('.quantity_button'),
                $price = $this
                    .closest('.quantity')
                    .siblings('.box-price')
                    .find('.price-current'),
                $priceFirst = $price.data('current'),
                current = parseInt($this.val(), 10),
                min = parseInt($this.attr('min'), 10),
                max = parseInt($this.attr('max'), 10)
            let min_check = min ? min : 1
            let max_check = max ? max : current + 1
            if (current <= max_check && current >= min_check) {
                $btn.attr('data-quantity', current)
                let $total = ($priceFirst * current).toFixed(2)
                $price.html($total)
            }

            MartApp.processUpdateCart($this)
        })
    }

    MartApp.addProductToWishlist = function() {
        MartApp.$body.on('click', '.wishlist-button .wishlist', function(e) {
            e.preventDefault()
            const $btn = $(e.currentTarget)
            $btn.addClass('loading')

            $.ajax({
                url: $btn.data('url'),
                method: 'POST',
                success: (res) => {
                    if (res.error) {
                        MartApp.showError(res.message)
                        return false
                    }

                    MartApp.showSuccess(res.message)
                    $('.btn-wishlist .header-item-counter').text(
                        res.data.count,
                    )
                    if (res.data?.added) {
                        $(
                            '.wishlist-button .wishlist[data-url="' + $btn.data('url') + '"]',
                        ).addClass('added-to-wishlist')
                    } else {
                        $(
                            '.wishlist-button .wishlist[data-url="' + $btn.data('url') + '"]',
                        ).removeClass('added-to-wishlist')
                    }
                },
                error: (res) => {
                    MartApp.showError(res.message)
                },
                complete: () => {
                    $btn.removeClass('loading')
                },
            })
        })
    }

    MartApp.addProductToCompare = function() {
        MartApp.$body.on('click', '.compare-button .compare', function(e) {
            e.preventDefault()
            const $btn = $(e.currentTarget)
            $btn.addClass('loading')

            $.ajax({
                url: $btn.data('url'),
                method: 'POST',
                success: (res) => {
                    if (res.error) {
                        MartApp.showError(res.message)
                        return false
                    }
                    MartApp.showSuccess(res.message)
                    $('.btn-compare .header-item-counter').text(res.data.count)
                },
                error: (res) => {
                    MartApp.showError(res.message)
                },
                complete: () => {
                    $btn.removeClass('loading')
                },
            })
        })
    }

    MartApp.addProductToCart = function() {
        MartApp.$body.on('click', 'form.cart-form button[type=submit]', function(e) {
            e.preventDefault()
            const $form = $(this).closest('form.cart-form')
            const $btn = $(this)
            $btn.addClass('loading')

            let data = $form.serializeArray()
            data.push({ name: 'checkout', value: $btn.prop('name') === 'checkout' ? 1 : 0 })

            $.ajax({
                type: 'POST',
                url: $form.prop('action'),
                data: $.param(data),
                success: (res) => {

                    if (res.error) {
                        MartApp.showError(res.message)
                        if (res.data.next_url !== undefined) {
                            window.location.href = res.data.next_url
                        }

                        return false
                    }

                    if (res.data.next_url !== undefined) {
                        window.location.href = res.data.next_url
                        return false
                    }

                    MartApp.showSuccess(res.message)
                    MartApp.loadAjaxCart()
                },
                error: (res) => {
                    MartApp.handleError(res, $form)
                },
                complete: () => {
                    $btn.removeClass('loading')
                },
            })
        })
    }

    MartApp.applyCouponCode = function() {
        $(document).on('keypress', '.form-coupon-wrapper .coupon-code', e => {
            if (e.key === 'Enter') {
                e.preventDefault()
                e.stopPropagation()
                $(e.currentTarget).closest('.form-coupon-wrapper').find('.btn-apply-coupon-code').trigger('click')
                return false
            }
        })

        $(document).on('click', '.btn-apply-coupon-code', e => {
            e.preventDefault()
            let _self = $(e.currentTarget)

            $.ajax({
                url: _self.data('url'),
                type: 'POST',
                data: {
                    coupon_code: _self.closest('.form-coupon-wrapper').find('.coupon-code').val(),
                },
                beforeSend: () => {
                    _self.prop('disabled', true).addClass('loading')
                },
                success: (res) => {
                    if (!res.error) {
                        $('.cart-page-content').load(window.location.href + '?applied_coupon=1 .cart-page-content > *', function() {
                            _self.prop('disabled', false).removeClass('loading')
                            MartApp.showSuccess(res.message)
                        })
                    } else {
                        MartApp.showError(res.message)
                    }
                },
                error: data => {
                    MartApp.handleError(data)
                },
                complete: (res) => {
                    if (!(res.status == 200 && res?.responseJSON?.error == false)) {
                        _self.prop('disabled', false).removeClass('loading')
                    }
                },
            })
        })

        $(document).on('click', '.btn-remove-coupon-code', e => {
            e.preventDefault()
            const _self = $(e.currentTarget)
            const buttonText = _self.text()
            _self.text(_self.data('processing-text'))

            $.ajax({
                url: _self.data('url'),
                type: 'POST',
                success: (res) => {
                    if (!res.error) {
                        $('.cart-page-content').load(window.location.href + ' .cart-page-content > *', function() {
                            _self.text(buttonText)
                        })
                    } else {
                        MartApp.showError(res.message)
                    }
                },
                error: data => {
                    MartApp.handleError(data)
                },
                complete: (res) => {
                    if (!(res.status == 200 && res?.responseJSON?.error == false)) {
                        _self.text(buttonText)
                    }
                },
            })
        })
    }

    MartApp.loadAjaxCart = function() {
        if (window.siteConfig?.ajaxCart) {
            $.ajax({
                url: window.siteConfig.ajaxCart,
                method: 'GET',
                success: function(res) {
                    if (!res.error) {
                        $('.mini-cart-content .widget-shopping-cart-content').html(res.data.html)
                        $('.btn-shopping-cart .header-item-counter').text(res.data.count)
                        $('.cart--mini .cart-price-total .cart-amount span').text(res.data.total_price)
                        $('.menu--footer .icon-cart .cart-counter').text(res.data.count)
                        MartApp.lazyLoad($('.mini-cart-content')[0])
                    }
                },
            })
        }
    }

    MartApp.changeInputInSearchForm = function(parseParams) {
        isReadySubmitTrigger = false
        MartApp.$formSearch
            .find('input, select, textarea')
            .each(function(e, i) {
                const $el = $(i)
                const name = $el.attr('name')
                let value = parseParams[name] || null
                const type = $el.attr('type')
                switch (type) {
                    case 'checkbox':
                        $el.prop('checked', false)
                        if (Array.isArray(value)) {
                            $el.prop('checked', value.includes($el.val()))
                        } else {
                            $el.prop('checked', !!value)
                        }
                        break
                    default:
                        if ($el.is('[name=max_price]')) {
                            $el.val(value || $el.data('max'))
                        } else if ($el.is('[name=min_price]')) {
                            $el.val(value || $el.data('min'))
                        } else if ($el.val() != value) {
                            $el.val(value)
                        }
                        break
                }
            })
        isReadySubmitTrigger = true
    }

    MartApp.convertFromDataToArray = function(formData) {
        let data = []
        formData.forEach(function(obj) {
            if (obj.value) {
                // break with price
                if (['min_price', 'max_price'].includes(obj.name)) {
                    const dataValue = MartApp.$formSearch
                        .find('input[name=' + obj.name + ']')
                        .data(obj.name.substring(0, 3))
                    if (dataValue == parseInt(obj.value)) {
                        return
                    }
                }
                data.push(obj)
            }
        })
        return data
    }

    let isReadySubmitTrigger = true

    MartApp.productsFilter = function() {
        MartApp.widgetProductCategories = '.widget-product-categories'
        MartApp.$widgetProductCategories = $(MartApp.widgetProductCategories)

        $(document).on('change', '#products-filter-form .product-filter-item', function() {
            if (isReadySubmitTrigger) {
                $(this).closest('form').trigger('submit')
            }
        })

        function openCategoryFilter($li) {
            if (!$li) {
                const $categories = $('.widget-product-categories').find('li a.active')
                if ($categories.length) {
                    MartApp.$widgetProductCategories.find('.widget-layered-nav-list > ul > li.category-filter').addClass('d-none')
                } else {
                    MartApp.$widgetProductCategories.find('.widget-layered-nav-list > ul > li.category-filter').removeClass('d-none')
                    MartApp.$widgetProductCategories.find('.show-all-product-categories').addClass('d-none')
                }
                MartApp.$widgetProductCategories.find('.widget-layered-nav-list li.category-filter').removeClass('opened')

                $categories.map(function(e, i) {
                    const $parent = $(i).closest('li.category-filter').closest('ul').closest('li.category-filter')
                    $parent.removeClass('d-none')

                    if ($parent.length) {
                        openCategoryFilter($parent)
                        MartApp.$widgetProductCategories.find('.show-all-product-categories').removeClass('d-none')
                    } else {
                        MartApp.$widgetProductCategories.find('li.category-filter').removeClass('d-none')
                        MartApp.$widgetProductCategories.find('.show-all-product-categories').addClass('d-none')
                    }
                })
            } else if ($li.length) {
                $li.addClass('opened')
                $li.removeClass('d-none')
                $li.find('> .widget-layered-nav-list__item .nav-list__item-link').removeClass('active')

                if ($li.closest('ul').closest('li.category-filter').length) {
                    openCategoryFilter($li.closest('ul').closest('li.category-filter'))
                }
            }

            MartApp.$widgetProductCategories.find('.loading-skeleton').removeClass('loading-skeleton')
        }

        openCategoryFilter()

        $('.catalog-toolbar__ordering input[name=sort-by]').on('change', function(e) {
            MartApp.$formSearch.find('input[name=sort-by]').val($(e.currentTarget).val())
            MartApp.$formSearch.trigger('submit')
        })

        MartApp.$body.on('click', '.cat-menu-close', function(e) {
            e.preventDefault()
            $(this).closest('li').toggleClass('opened')
        })

        $(document).on('click', MartApp.widgetProductCategories + ' li a', function(e) {
            e.preventDefault()
            const $this = $(e.currentTarget)
            const activated = $this.hasClass('active')
            const $parent = $this.closest(MartApp.widgetProductCategories)
            $parent.find('li a').removeClass('active')
            $this.addClass('active')
            let categoryId = $this.data('id')

            if (categoryId) {
                let $item = $parent.find('.widget-layered-nav-list .nav-list__item-link[data-id=' + categoryId + ']')
                $item.addClass('active')

                openCategoryFilter()
            } else {
                $parent.find('.widget-layered-nav-list .category-filter').removeClass('opened d-none')
                $parent.find('.show-all-product-categories').addClass('d-none')
            }
            const $form = $this.closest(MartApp.formSearch)

            $form.find('.widget-product-brands ul li, .dropdown-swatches-wrapper, .text-swatches-wrapper, .visual-swatches-wrapper').each(function(i, el) {
                let $el = $(el)
                let categories = $el.data('categories')
                if (categories && Array.isArray(categories) && categories.length) {
                    if (!categories.includes(categoryId)) {
                        $el.addClass('d-none')
                        $el.find('input').prop('checked', false)
                        $el.find('select').val('')
                    } else {
                        $el.removeClass('d-none')
                    }
                }
            })

            const $input = $parent.find('input[name=\'categories[]\']')
            if ($input.length) {
                if (activated) {
                    $this.removeClass('active')
                    $input.val('')
                } else {
                    $input.val(categoryId)
                }
                $input.trigger('change')
            } else {
                let href = $this.attr('href')

                MartApp.$formSearch.attr('action', href).trigger('submit')
            }
        })

        $(document).on('submit', '#products-filter-form', function(e) {
            e.preventDefault()
            const $form = $(e.currentTarget)
            const formData = $form.serializeArray()
            let data = MartApp.convertFromDataToArray(formData)
            let uriData = []

            // Paginate
            const $elPage = MartApp.$productListing.find('input[name=page]')
            if ($elPage.val()) {
                data.push({ name: 'page', value: $elPage.val() })
            }

            // Without "s" param
            data.map(function(obj) {
                uriData.push(encodeURIComponent(obj.name) + '=' + obj.value)
            })

            const nextHref =
                $form.attr('action') +
                (uriData && uriData.length ? '?' + uriData.join('&') : '')

            // add to params get to popstate not show json
            data.push({ name: '_', value: +new Date() })

            $.ajax({
                url: $form.attr('action'),
                type: 'GET',
                data: data,
                beforeSend: function() {
                    // Show loading before sending
                    MartApp.$productListing.find('.loading').show()
                    // Animation scroll to filter button
                    $('html, body').animate({ scrollTop: MartApp.$productListing.offset().top - 200 }, 500)
                    // Change price step;
                    const priceStep = MartApp.$formSearch.find('.nonlinear')
                    if (priceStep.length) {
                        priceStep[0].noUiSlider.set([
                            MartApp.$formSearch
                                .find('input[name=min_price]')
                                .val(),
                            MartApp.$formSearch
                                .find('input[name=max_price]')
                                .val(),
                        ])
                    }
                    MartApp.toggleSidebarFilterProducts()
                },
                success: function(res) {
                    if (! res.error) {
                        MartApp.$productListing.html(res.data)

                        const total = res.message
                        if (total && $('.products-found').length) {
                            $('.products-found').html('<span class="text-primary me-1">' + total.substr(0, total.indexOf(' ')) +
                                '</span>' + total.substr(total.indexOf(' ') + 1))
                        }

                        MartApp.lazyLoad(MartApp.$productListing[0])
                        let title = res.additional?.category?.name || MartApp.$formSearch.data('title')
                        $('h1.catalog-header__title').text(title)
                        document.title = title

                        if (res.additional?.breadcrumb) {
                            $('.page-breadcrumbs div').html(res.additional.breadcrumb)
                        }

                        if (res.additional?.filters_html) {
                            MartApp.$formSearch.html(res.additional.filters_html)
                            MartApp.$formSearch.find('.loading-skeleton').removeClass('loading-skeleton')
                            MartApp.filterSlider()
                        }

                        if (nextHref != window.location.href) {
                            window.history.pushState(
                                data,
                                res.message,
                                nextHref,
                            )
                        }
                    } else {
                        MartApp.showError(res.message || 'Opp!')
                    }
                },
                error: function(res) {
                    MartApp.handleError(res)
                },
                complete: function() {
                    MartApp.$productListing.find('.loading').hide()
                },
            })
        })

        if (MartApp.$formSearch.length) {
            window.addEventListener(
                'popstate',
                function() {
                    let url = window.location.origin + window.location.pathname
                    MartApp.$formSearch.attr('action', url)
                    const parseParams = MartApp.parseParamsSearch()
                    MartApp.changeInputInSearchForm(parseParams)
                    MartApp.$formSearch.trigger('submit')
                },
                false,
            )
        }

        $(document).on(
            'click',
            MartApp.productListing + ' .pagination a',
            function(e) {
                e.preventDefault()
                let url = new URL($(e.currentTarget).attr('href'))
                let page = url.searchParams.get('page')
                MartApp.$productListing.find('input[name=page]').val(page)
                MartApp.$formSearch.trigger('submit')
            },
        )
    }

    MartApp.parseParamsSearch = function(query, includeArray = false) {
        let pairs = query || window.location.search.substring(1)
        let re = /([^&=]+)=?([^&]*)/g
        let decodeRE = /\+/g  // Regex for replacing addition symbol with a space
        let decode = function(str) {
            return decodeURIComponent(str.replace(decodeRE, ' '))
        }
        let params = {}, e
        while (e = re.exec(pairs)) {
            let k = decode(e[1]), v = decode(e[2])
            if (k.substring(k.length - 2) == '[]') {
                if (includeArray) {
                    k = k.substring(0, k.length - 2)
                }
                (params[k] || (params[k] = [])).push(v)
            } else params[k] = v
        }
        return params
    }

    MartApp.searchProducts = function() {
        $('body').on('click', function(e) {
            if (!$(e.target).closest('.form--quick-search').length) {
                $('.panel--search-result').removeClass('active')
            }
        })

        let currentRequest = null
        $('.form--quick-search .input-search-product')
            .on('keyup', function() {
                const $form = $(this).closest('form')
                ajaxSearchProduct($form)
            })

        $('.form--quick-search .product-category-select').on('change', function() {
            const $form = $(this).closest('form')
            ajaxSearchProduct($form)
        })

        $('.form--quick-search').on('click', '.loadmore', function(e) {
            e.preventDefault()
            const $form = $(this).closest('form')
            $(this).addClass('loading')
            ajaxSearchProduct($form, $(this).attr('href'))
        })

        function ajaxSearchProduct($form, url = null) {
            const $panel = $form.find('.panel--search-result')
            const k = $form.find('.input-search-product').val()
            if (!k) {
                $panel.html('').removeClass('active')
                return
            }
            const $button = $form.find('button[type=submit]')

            currentRequest = $.ajax({
                type: 'GET',
                url: url || $form.data('ajax-url'),
                data: url ? [] : $form.serialize(),
                beforeSend: function() {
                    if (currentRequest != null) {
                        currentRequest.abort()
                    }

                    $button.addClass('loading')
                },
                success: (res) => {
                    if (!res.error) {
                        if (url) {
                            const $content = $('<div>' + res.data + '</div>')
                            $panel.find('.panel__content').find('.loadmore-container').remove()
                            $panel.find('.panel__content').append($content.find('.panel__content').contents())
                        } else {
                            $panel.html(res.data).addClass('active')
                        }
                    } else {
                        $panel.html('').removeClass('active')
                    }

                    $button.removeClass('loading')
                },
                error: () => {
                    $button.removeClass('loading')
                },
            })
        }
    }

    MartApp.processUpdateCart = function($this) {
        const $form = $('.cart-page-content').find('.form--shopping-cart')

        if (!$form.length) {
            return false
        }

        $.ajax({
            type: 'POST',
            cache: false,
            url: $form.prop('action'),
            data: new FormData($form[0]),
            contentType: false,
            processData: false,
            beforeSend: () => {
                $this.addClass('loading')
            },
            success: (res) => {
                if (res.error) {
                    MartApp.showError(res.message)
                    return false
                }

                $('.cart-page-content').load(window.siteConfig.cartUrl + ' .cart-page-content > *', function() {
                    MartApp.lazyLoad($('.cart-page-content')[0])
                })

                MartApp.loadAjaxCart()

                MartApp.showSuccess(res.message)
            },
            error: (res) => {
                $this.closest('.ps-table--shopping-cart').removeClass('content-loading')
                MartApp.handleError(res)
            },
            complete: () => {
                $this.removeClass('loading')
            },
        })
    }

    MartApp.ajaxUpdateCart = function(_self) {
        $(document).on('click', '.cart-page-content .update_cart', function(e) {
            e.preventDefault()
            const $this = $(e.currentTarget)

            MartApp.processUpdateCart($this)
        })
    }

    MartApp.removeCartItem = function() {
        $(document).on('click', '.remove-cart-item', function(event) {
            event.preventDefault()
            let _self = $(this)

            $.ajax({
                url: _self.data('url'),
                method: 'GET',
                beforeSend: () => {
                    _self.addClass('loading')
                },
                success: (res) => {
                    if (res.error) {
                        MartApp.showError(res.message)
                        return false
                    }

                    const $cartContent = $('.cart-page-content')

                    if ($cartContent.length && window.siteConfig?.cartUrl) {
                        $cartContent.load(window.siteConfig.cartUrl + ' .cart-page-content > *', function() {
                            MartApp.lazyLoad($cartContent[0])
                        })
                    }

                    MartApp.loadAjaxCart()
                },
                error: (res) => {
                    MartApp.handleError(res)
                },
                complete: () => {
                    _self.removeClass('loading')
                },
            })
        })
    }

    MartApp.removeWishlistItem = function() {
        $(document).on('click', '.remove-wishlist-item', function(event) {
            event.preventDefault()
            let _self = $(this)

            $.ajax({
                url: _self.data('url'),
                method: 'POST',
                data: {
                    _method: 'DELETE',
                },
                beforeSend: () => {
                    _self.addClass('loading')
                },
                success: (res) => {
                    if (res.error) {
                        MartApp.showError(res.message)
                    } else {
                        MartApp.showSuccess(res.message)
                        $('.btn-wishlist .header-item-counter').text(res.data.count)
                        _self.closest('tr').remove()
                    }
                },
                error: (res) => {
                    MartApp.handleError(res)
                },
                complete: () => {
                    _self.removeClass('loading')
                },
            })
        })
    }

    MartApp.removeCompareItem = function() {
        $(document).on('click', '.remove-compare-item', function(event) {
            event.preventDefault()
            let _self = $(this)

            $.ajax({
                url: _self.data('url'),
                method: 'POST',
                data: {
                    _method: 'DELETE',
                },
                beforeSend: () => {
                    _self.addClass('loading')
                },
                success: (res) => {
                    if (res.error) {
                        MartApp.showError(res.message)
                    } else {
                        MartApp.showSuccess(res.message)
                        $('.btn-compare .header-item-counter').text(res.data.count)
                        $('.compare-page-content').load(window.location.href + ' .compare-page-content > *')
                    }
                },
                error: (res) => {
                    MartApp.handleError(res)
                },
                complete: () => {
                    _self.removeClass('loading')
                },
            })
        })
    }

    MartApp.handleTabBootstrap = function() {
        let hash = window.location.hash
        if (hash) {
            let tabTriggerEl = $('a[href="' + hash + '"]')
            if (tabTriggerEl.length) {
                let tab = new bootstrap.Tab(tabTriggerEl[0])
                tab.show()
            }
        }
    }

    MartApp.filterSlider = function() {
        $(document).find('.nonlinear').each(function(index, element) {
            let $element = $(element)
            let min = $element.data('min')
            let max = $element.data('max')
            let $wrapper = $(element).closest('.nonlinear-wrapper')
            noUiSlider.create(element, {
                connect: true,
                behaviour: 'tap',
                start: [
                    $wrapper.find('.product-filter-item-price-0').val(),
                    $wrapper.find('.product-filter-item-price-1').val(),
                ],
                range: {
                    min: min,
                    '10%': max * 0.1,
                    '20%': max * 0.2,
                    '30%': max * 0.3,
                    '40%': max * 0.4,
                    '50%': max * 0.5,
                    '60%': max * 0.6,
                    '70%': max * 0.7,
                    '80%': max * 0.8,
                    '90%': max * 0.9,
                    max: max,
                },
            })

            let nodes = [
                $wrapper.find('.slider__min'),
                $wrapper.find('.slider__max'),
            ]

            element.noUiSlider.on('update', function(values, handle) {
                nodes[handle].html(MartApp.numberFormat(values[handle]))
            })

            element.noUiSlider.on('change', function(values, handle) {
                $wrapper
                    .find('.product-filter-item-price-' + handle)
                    .val(Math.round(values[handle]))
                    .trigger('change')
            })
        })
    }

    MartApp.numberFormat = function(
        number,
        decimals,
        dec_point,
        thousands_sep,
    ) {
        let n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = typeof thousands_sep === 'undefined' ? ',' : thousands_sep,
            dec = typeof dec_point === 'undefined' ? '.' : dec_point,
            toFixedFix = function(n, prec) {
                // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                let k = Math.pow(10, prec)
                return Math.round(n * k) / k
            },
            s = (prec ? toFixedFix(n, prec) : Math.round(n))
                .toString()
                .split('.')

        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
        }

        if ((s[1] || '').length < prec) {
            s[1] = s[1] || ''
            s[1] += new Array(prec - s[1].length + 1).join('0')
        }
        return s.join(dec)
    }

    MartApp.submitReviewProduct = function() {
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
            let $uploadText = $('.image-upload__text')
            const maxFiles = $(input).data('max-files')
            let filesAmount = input.files.length

            if (maxFiles) {
                if (filesAmount >= maxFiles) {
                    $uploadText.closest('.image-upload__uploader-container').addClass('d-none')
                } else {
                    $uploadText.closest('.image-upload__uploader-container').removeClass('d-none')
                }
                $uploadText.text(filesAmount + '/' + maxFiles)
            } else {
                $uploadText.text(filesAmount)
            }
            const viewerList = $('.image-viewer__list')
            const $template = $('#review-image-template').html()

            viewerList.addClass('is-loading')
            viewerList.find('.image-viewer__item').remove()

            if (filesAmount) {
                for (let i = filesAmount - 1; i >= 0; i--) {
                    viewerList.prepend($template.replace('__id__', i))
                }
                for (let j = filesAmount - 1; j >= 0; j--) {
                    let reader = new FileReader()
                    reader.onload = function(event) {
                        viewerList
                            .find('.image-viewer__item[data-id=' + j + ']')
                            .find('img')
                            .attr('src', event.target.result)
                    }
                    reader.readAsDataURL(input.files[j])
                }
            }
            viewerList.removeClass('is-loading')
        }

        $(document).on('change', '.form-review-product input[type=file]', function(event) {
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

        $(document).on('click', '.form-review-product .image-viewer__icon-remove', function(event) {
            event.preventDefault()
            const $this = $(event.currentTarget)
            let id = $this.closest('.image-viewer__item').data('id')
            imagesReviewBuffer.splice(id, 1)

            let input = $('.form-review-product input[type=file]')[0]
            setImagesFormReview(input)
        })

        if (sessionStorage.reloadReviewsTab) {
            if ($('#product-detail-tabs a[href="#product-reviews"]').length) {
                new bootstrap.Tab($('#product-detail-tabs a[href="#product-reviews"]')[0]).show()
            }
            sessionStorage.reloadReviewsTab = false
        }

        $(document).on('click', '.form-review-product button[type=submit]', function(e) {
            e.preventDefault()
            e.stopPropagation()
            const $this = $(e.currentTarget)

            const $form = $(this).closest('form')
            $.ajax({
                type: 'POST',
                cache: false,
                url: $form.prop('action'),
                data: new FormData($form[0]),
                contentType: false,
                processData: false,
                beforeSend: () => {
                    $this.prop('disabled', true).addClass('loading')
                },
                success: (res) => {
                    if (!res.error) {
                        $form.find('select').val(0)
                        $form.find('textarea').val('')

                        MartApp.showSuccess(res.message)

                        setTimeout(function() {
                            sessionStorage.reloadReviewsTab = true
                            window.location.reload()
                        }, 1500)
                    } else {
                        MartApp.showError(res.message)
                    }
                },
                error: (res) => {
                    MartApp.handleError(res, $form)
                },
                complete: () => {
                    $this.prop('disabled', false).removeClass('loading')
                },
            })
        })

    }

    MartApp.vendorRegisterForm = function() {
        $(document).on('click', 'input[name=is_vendor]', function() {
            if ($(this).val() == 1) {
                $('.show-if-vendor').slideDown().show()
            } else {
                $('.show-if-vendor').slideUp(500)
                $(this).closest('form').find('button[type=submit]').prop('disabled', false)
            }
        })

        $('#shop-url-register')
            .on('keyup', function() {
                let displayURL = $(this).closest('.form-group').find('span small')
                displayURL.html(displayURL.data('base-url') + '/<strong>' + $(this).val().toLowerCase() + '</strong>')
            })
            .on('change', function() {
                const $this = $(this)
                const url = $this.val()
                if (!url) {
                    return
                }
                let displayURL = $this.closest('.form-group').find('span small')

                $.ajax({
                    url: $this.data('url'),
                    type: 'POST',
                    data: { url },
                    beforeSend: () => {
                        $this.prop('disabled', true)
                        $this.closest('form').find('button[type=submit]').prop('disabled', true)
                    },
                    success: (res) => {
                        if (res.error) {
                            $this.addClass('is-invalid').removeClass('is-valid')
                            $('.shop-url-status').removeClass('text-success').addClass('text-danger').text(res.message)
                        } else {
                            $this.addClass('is-valid').removeClass('is-invalid')
                            $('.shop-url-status').removeClass('text-danger').addClass('text-success').text(res.message)
                            $this.closest('form').find('button[type=submit]').prop('disabled', false)
                        }
                        if (res.data?.slug) {
                            displayURL.html(displayURL.data('base-url') + '/<strong>' + res.data?.slug + '</strong>')
                        }
                    },
                    error: () => {
                    },
                    complete: () => {
                        $this.prop('disabled', false)
                    },
                })
            })
    }

    MartApp.customerDashboard = function() {
        if ($.fn.datepicker) {
            $('#date_of_birth').datepicker({
                format: 'yyyy-mm-dd',
                orientation: 'bottom',
            })
        }

        $('#avatar').on('change', event => {
            let input = event.currentTarget
            if (input.files && input.files[0]) {
                let reader = new FileReader()
                reader.onload = e => {
                    $('.userpic-avatar')
                        .attr('src', e.target.result)
                }
                reader.readAsDataURL(input.files[0])
            }
        })

        $(document).on('click', '.btn-trigger-delete-address', function(event) {
            event.preventDefault()
            $('.btn-confirm-delete').data('url', $(this).data('url'))
            $('#confirm-delete-modal').modal('show')
        })

        $(document).on('click', '.btn-confirm-delete', function(event) {
            event.preventDefault()
            let $current = $(this)
            $.ajax({
                url: $current.data('url'),
                type: 'GET',
                beforeSend: () => {
                    $current.addClass('loading')
                },
                success: (res) => {
                    $current.closest('.modal').modal('hide')
                    if (res.error) {
                        MartApp.showError(res.message)
                    } else {
                        MartApp.showSuccess(res.message)
                        $('.btn-trigger-delete-address[data-url="' + $current.data('url') + '"]').closest('.col').remove()
                    }
                },
                error: (res) => {
                    MartApp.handleError(res)
                },
                complete: () => {
                    $current.removeClass('loading')
                },
            })
        })
    }

    MartApp.newsletterForm = function() {
        $(document).on('submit', 'form.subscribe-form', function(e) {
            e.preventDefault()
            e.stopPropagation()
            const $this = $(e.currentTarget)

            let _self = $this.find('button[type=submit]')

            $.ajax({
                type: 'POST',
                cache: false,
                url: $this.prop('action'),
                data: new FormData($this[0]),
                contentType: false,
                processData: false,
                beforeSend: () => {
                    _self.prop('disabled', true).addClass('button-loading')
                },
                success: (res) => {
                    if (typeof refreshRecaptcha !== 'undefined') {
                        refreshRecaptcha()
                    }

                    if (!res.error) {
                        $this.find('input[type=email]').val('')
                        MartApp.showSuccess(res.message)
                    } else {
                        MartApp.showError(res.message)
                    }
                },
                error: (res) => {
                    if (typeof refreshRecaptcha !== 'undefined') {
                        refreshRecaptcha()
                    }
                    MartApp.handleError(res)
                },
                complete: () => {
                    _self.prop('disabled', false).removeClass('button-loading')
                },
            })
        })
    }

    MartApp.contactSellerForm = function() {
        $(document).on('click', 'form.form-contact-store button[type=submit]', function(e) {
            e.preventDefault()
            e.stopPropagation()
            const $this = $(e.currentTarget)

            let $form = $this.closest('form')

            $.ajax({
                type: 'POST',
                cache: false,
                url: $form.prop('action'),
                data: new FormData($form[0]),
                contentType: false,
                processData: false,
                beforeSend: () => {
                    $this.prop('disabled', true).addClass('button-loading')
                },
                success: (res) => {
                    if (typeof refreshRecaptcha !== 'undefined') {
                        refreshRecaptcha()
                    }

                    if (!res.error) {
                        $form.find('input[type=email]:not(:disabled)').val('')
                        $form.find('input[type=text]:not(:disabled)').val('')
                        $form.find('textarea').val('')
                        MartApp.showSuccess(res.message)
                    } else {
                        MartApp.showError(res.message)
                    }
                },
                error: (res) => {
                    if (typeof refreshRecaptcha !== 'undefined') {
                        refreshRecaptcha()
                    }
                    MartApp.handleError(res)
                },
                complete: () => {
                    $this.prop('disabled', false).removeClass('button-loading')
                },
            })
        })
    }

    MartApp.recentlyViewedProducts = function() {
        MartApp.$body.find('.header-recently-viewed').each(function() {
            const $el = $(this)
            let loading
            $el.hover(function() {
                const $recently = $el.find('.recently-viewed-products')
                if ($el.data('loaded') || loading) {
                    return
                }
                const url = $el.data('url')
                if (!url) {
                    return
                }
                $.ajax({
                    type: 'GET',
                    url,
                    beforeSend: () => {
                        loading = true
                    },
                    success: (res) => {
                        if (!res.error) {
                            $recently.html(res.data)

                            if ($recently.find('.product-list li').length > 0) {
                                MartApp.slickSlide($recently.find('.product-list'))
                            }
                            $el.data('loaded', true).find('.loading--wrapper').addClass('d-none')
                        } else {
                            MartApp.showError(res.message)
                        }
                    },
                    error: (res) => {
                        MartApp.handleError(res)
                    },
                    complete: () => {
                        loading = false
                    },
                })
            })
        })
    }

    MartApp.showNotice = function(messageType, message) {
        MartApp.$toastLive = $('#toast-notifications')
        if (MartApp.$toastLive.length) {
            MartApp.toast = new bootstrap.Toast(MartApp.$toastLive)
        }

        MartApp.$toastLive.removeClass(function(index, className) {
            return (className.match(/(^|\s)toast--\S+/g) || []).join(' ')
        })
        MartApp.$toastLive.addClass('toast--' + messageType)
        MartApp.$toastLive.find('.toast-body .toast-message').html(message)
        MartApp.toast.show()
    }

    MartApp.showError = function(message) {
        this.showNotice('error', message)
    }

    MartApp.showSuccess = function(message) {
        this.showNotice('success', message)
    }

    MartApp.handleError = (data) => {
        if (typeof data.errors !== 'undefined' && data.errors.length) {
            MartApp.handleValidationError(data.errors)
        } else if (typeof data.responseJSON !== 'undefined') {
            if (typeof data.responseJSON.errors !== 'undefined') {
                if (data.status === 422) {
                    MartApp.handleValidationError(data.responseJSON.errors)
                }
            } else if (typeof data.responseJSON.message !== 'undefined') {
                MartApp.showError(data.responseJSON.message)
            } else {
                MartApp.showError(data.responseJSON.join(', ').join(', '))
            }
        } else {
            MartApp.showError(data.statusText)
        }
    }

    MartApp.handleValidationError = (errors) => {
        let message = ''
        $.each(errors, (index, item) => {
            if (message !== '') {
                message += '<br />'
            }
            message += item
        })
        MartApp.showError(message)
    }

    MartApp.toggleViewProducts = function() {
        $(document).on('click', '.store-list-filter-button', function(e) {
            e.preventDefault()
            $('#store-listing-filter-form-wrap').toggle(500)
        })

        MartApp.$body.on('click', '.toolbar-view__icon a', function(e) {
            e.preventDefault()
            const $this = $(e.currentTarget)
            $this
                .closest('.toolbar-view__icon')
                .find('a')
                .removeClass('active')
            $this.addClass('active')
            $($this.data('target'))
                .removeClass($this.data('class-remove'))
                .addClass($this.data('class-add'))

            MartApp.$formSearch
                .find('input[name=layout]')
                .val($this.data('layout'))

            const params = new URLSearchParams(window.location.search)
            params.set('layout', $this.data('layout'))
            const nextHref = window.location.protocol + '//' + window.location.host + window.location.pathname + '?' + params.toString()
            if (nextHref != window.location.href) {
                window.history.pushState(
                    MartApp.$productListing.html(),
                    '',
                    nextHref,
                )
            }
        })
    }

    MartApp.toolbarOrderingProducts = function() {
        MartApp.$body.on(
            'click',
            '.catalog-toolbar__ordering .dropdown .dropdown-menu a',
            function(e) {
                e.preventDefault()
                const $this = $(e.currentTarget)
                const $parent = $this.closest('.dropdown')
                $parent.find('li').removeClass('active')
                $this.closest('li').addClass('active')
                $parent.find('a[data-bs-toggle=dropdown').html($this.html())
                $this.closest('.catalog-toolbar__ordering').find('input[name=sort-by]').val($this.data('value')).trigger('change')
            },
        )
    }

    MartApp.backToTop = function() {
        let scrollPos = 0
        let element = $('#back2top')
        $(window).scroll(function() {
            let scrollCur = $(window).scrollTop()
            if (scrollCur > scrollPos) {
                // scroll down
                if (scrollCur > 500) {
                    element.addClass('active')
                } else {
                    element.removeClass('active')
                }
            } else {
                // scroll up
                element.removeClass('active')
            }

            scrollPos = scrollCur
        })

        element.on('click', function() {
            $('html, body').animate(
                {
                    scrollTop: '0px',
                },
                0,
            )
        })
    }

    MartApp.stickyHeader = function() {
        let header = $('.header-js-handler')
        let checkpoint = header.height()
        header.each(function() {
            if ($(this).data('sticky') === true) {
                let el = $(this)
                $(window).scroll(function() {
                    let currentPosition = $(this).scrollTop()
                    if (currentPosition > checkpoint) {
                        el.addClass('header--sticky')
                    } else {
                        el.removeClass('header--sticky')
                    }
                })
            }
        })
    }

    MartApp.stickyAddToCart = function() {
        let $headerProduct = $('.header--product')
        $(window).scroll(function() {
            let currentPosition = $(this).scrollTop()
            if (currentPosition > 50) {
                $headerProduct.addClass('header--sticky')
            } else {
                $headerProduct.removeClass('header--sticky')
            }
        })

        $('.header--product ul li > a ').on('click', function(e) {
            e.preventDefault()
            let target = $(this).attr('href')
            $(this)
                .closest('li')
                .siblings('li')
                .removeClass('active')
            $(this)
                .closest('li')
                .addClass('active')
            $(target)
                .closest('.product-detail-tabs')
                .find('a')
                .removeClass('active')

            $(target).addClass('active')
            $('.header--product ul li').removeClass('active')
            $('.header--product ul li a[href="' + target + '"]').closest('li').addClass('active')

            $('#product-detail-tabs-content > .tab-pane').removeClass('active show')
            $($(target).attr('href')).addClass('active show')

            $('html, body').animate(
                {
                    scrollTop: ($(target).offset().top - $('.header--product .navigation').height() - 165) + 'px',
                },
                0,
            )
        })

        const $trigger = $('.product-details .entry-product-header'),
            $stickyBtn = $('.sticky-atc-wrap')

        if ($stickyBtn.length && $trigger.length && $(window).width() < 768) {
            let summaryOffset = $trigger.offset().top + $trigger.outerHeight(),
                _footer = $('.footer-mobile'),
                off_footer = 0,
                ck_footer = _footer.length > 0

            const stickyAddToCartToggle = function() {
                let windowScroll = $(window).scrollTop(),
                    windowHeight = $(window).height(),
                    documentHeight = $(document).height()
                if (ck_footer) {
                    off_footer = _footer.offset().top - _footer.height()
                } else {
                    off_footer = windowScroll
                }
                if (windowScroll + windowHeight === documentHeight || summaryOffset > windowScroll || windowScroll > off_footer) {
                    $stickyBtn.removeClass('sticky-atc-shown')
                } else if (summaryOffset < windowScroll && windowScroll + windowHeight !== documentHeight) {
                    $stickyBtn.addClass('sticky-atc-shown')
                }
            }

            stickyAddToCartToggle()

            $(window).scroll(stickyAddToCartToggle)
        }
    }

    MartApp.reviewList = function() {
        let $reviewListWrapper = MartApp.$body.find('.comment-list')
        const $loadingSpinner = MartApp.$body.find('.loading-spinner')

        $loadingSpinner.addClass('d-none')

        const fetchData = (url, hasAnimation = false) => {
            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function() {
                    $loadingSpinner.removeClass('d-none')

                    if (hasAnimation) {
                        $('html, body').animate({
                            scrollTop: `${$('.product-reviews-container').offset().top}px`,
                        }, 1500)
                    }
                },
                success: function(res) {
                    $reviewListWrapper.html(res.data)
                    $('.product-reviews-container .product-reviews-header').html(res.message)

                    let $galleries = $('.product-reviews-container .review-images')
                    if ($galleries.length) {
                        $galleries.map((index, value) => {
                            if (!$(value).data('lightGallery')) {
                                $(value).lightGallery({
                                    selector: 'a',
                                    thumbnail: true,
                                    share: false,
                                    fullScreen: false,
                                    autoplay: false,
                                    autoplayControls: false,
                                    actualSize: false,
                                })
                            }
                        })
                    }
                }, complete: function() {
                    $loadingSpinner.addClass('d-none')
                },
            })
        }

        if ($reviewListWrapper.length < 1) {
            return
        }

        fetchData($reviewListWrapper.data('url'))

        $reviewListWrapper.on('click', '.pagination ul li.page-item a', function(e) {
            e.preventDefault()

            const href = $(this).attr('href')

            if (href === '#') {
                return
            }

            fetchData(href, true)
        })
    }

    $(function() {
        MartApp.init()

        window.onBeforeChangeSwatches = function(data, $attrs) {
            const $product = $attrs.closest('.product-details')
            const $form = $product.find('.cart-form')

            $product.find('.error-message').hide()
            $product.find('.success-message').hide()
            $product.find('.number-items-available').html('').hide()
            const $submit = $form.find('button[type=submit]')
            $submit.addClass('loading')

            if (data && data.attributes) {
                $submit.prop('disabled', true)
            }
        }

        window.onChangeSwatchesSuccess = function(res, $attrs) {
            const $product = $attrs.closest('.product-details')
            const $form = $product.find('.cart-form')
            const $footerCartForm = $('.footer-cart-form')
            $product.find('.error-message').hide()
            $product.find('.success-message').hide()

            if (res) {
                let $submit = $form.find('button[type=submit]')
                $submit.removeClass('loading')
                if (res.error) {
                    $submit.prop('disabled', true)
                    $product.find('.number-items-available').html('<span class="text-danger">(' + res.message + ')</span>').show()
                    $form.find('.hidden-product-id').val('')
                    $footerCartForm.find('.hidden-product-id').val('')
                } else {
                    const data = res.data
                    const $price = $(document).find('.js-product-content')
                    const $salePrice = $price.find('.product-price-sale')
                    const $originalPrice = $price.find('.product-price-original')

                    if (data.sale_price !== data.price) {
                        $salePrice.removeClass('d-none')
                        $originalPrice.addClass('d-none')
                    } else {
                        $salePrice.addClass('d-none')
                        $originalPrice.removeClass('d-none')
                    }

                    $salePrice.find('ins .amount').text(data.display_sale_price)
                    $salePrice.find('del .amount').text(data.display_price)
                    $originalPrice.find('.amount').text(data.display_sale_price)

                    if (data.sku) {
                        $product.find('.meta-sku .meta-value').text(data.sku)
                        $product.find('.meta-sku').removeClass('d-none')
                    } else {
                        $product.find('.meta-sku').addClass('d-none')
                    }

                    $form.find('.hidden-product-id').val(data.id)
                    $footerCartForm.find('.hidden-product-id').val(data.id)
                    $submit.prop('disabled', false)

                    if (data.error_message) {
                        $submit.prop('disabled', true)
                        $product.find('.number-items-available').html('<span class="text-danger">(' + data.error_message + ')</span>').show()
                    } else if (data.success_message) {
                        $product.find('.number-items-available').html(res.data.stock_status_html).show()
                    } else {
                        $product.find('.number-items-available').html('').hide()
                    }

                    const unavailableAttributeIds = data.unavailable_attribute_ids || []
                    $product.find('.attribute-swatch-item').removeClass('pe-none')
                    $product.find('.product-filter-item option').prop('disabled', false)
                    if (unavailableAttributeIds && unavailableAttributeIds.length) {
                        unavailableAttributeIds.map(function(id) {
                            let $item = $product.find('.attribute-swatch-item[data-id="' + id + '"]')
                            if ($item.length) {
                                $item.addClass('pe-none')
                                $item.find('input').prop('checked', false)
                            } else {
                                $item = $product.find('.product-filter-item option[data-id="' + id + '"]')
                                if ($item.length) {
                                    $item.prop('disabled', 'disabled').prop('selected', false)
                                }
                            }
                        })
                    }

                    const $gallery = $product.closest('.product-detail-container').find('.product-gallery')
                    if (!data.image_with_sizes.origin.length) {
                        data.image_with_sizes.origin.push(siteConfig.img_placeholder)
                    }
                    if (!data.image_with_sizes.thumb.length) {
                        data.image_with_sizes.thumb.push(siteConfig.img_placeholder)
                    }

                    let imageHtml = ''
                    data.image_with_sizes.origin.forEach(function(item) {
                        imageHtml += `<div class='product-gallery__image item'>
                                <a class='img-fluid-eq' href='${item}'>
                                    <div class='img-fluid-eq__dummy'></div>
                                    <div class='img-fluid-eq__wrap'>
                                        <img class='mx-auto' alt='${data.name}' title='${data.name}' src='${siteConfig.img_placeholder ? siteConfig.img_placeholder : item}' data-lazy='${item}'>
                                    </div>
                                </a>
                            </div>`
                    })

                    $gallery.find('.product-gallery__wrapper').slick('unslick').html(imageHtml)

                    let thumbHtml = ''
                    data.image_with_sizes.thumb.forEach(function(item) {
                        thumbHtml += `<div class='item'>
                            <div class='border p-1 m-1'>
                                <img class='lazyload' alt='${data.name}' title='${data.name}' src='${siteConfig.img_placeholder ? siteConfig.img_placeholder : item}' data-src='${item}' data-lazy='${item}'>
                            </div>
                        </div>`
                    })

                    $gallery.find('.product-gallery__variants').slick('unslick').html(thumbHtml)

                    MartApp.productGallery(true, $gallery)

                    MartApp.lightBox()
                }
            }
        }

        if (jQuery().mCustomScrollbar) {
            $('.ps-custom-scrollbar').mCustomScrollbar({
                theme: 'dark',
                scrollInertia: 0,
            })
        }

        $(document).on('click', '.toggle-show-more', function(event) {
            event.preventDefault()

            $('#store-short-description').fadeOut()

            $(this).hide()

            $('#store-content').slideDown(500)

            $('.toggle-show-less').show()
        })

        $(document).on('click', '.toggle-show-less', function(event) {
            event.preventDefault()

            $(this).hide()

            $('#store-content').slideUp(500)

            $('#store-short-description').fadeIn()

            $('.toggle-show-more').show()
        })

        let collapseBreadcrumb = function() {
            $('.page-breadcrumbs ol li').each(function() {
                let $this = $(this)
                if (!$this.is(':first-child') && !$this.is(':nth-child(2)') && !$this.is(':last-child')) {
                    if (!$this.is(':nth-child(3)')) {
                        $this.find('a').closest('li').hide()
                    } else {
                        $this.find('a').hide()
                        $this.find('.extra-breadcrumb-name').text('...').show()
                    }
                }
            })
        }

        if ($(window).width() < 768) {
            collapseBreadcrumb()
        }

        $(window).on('resize', function() {
            collapseBreadcrumb()
        })

        $('.product-entry-meta .anchor-link').on('click', function(e) {
            e.preventDefault()
            let target = $(this).attr('href')

            $('#product-detail-tabs a').removeClass('active')
            $(target).addClass('active')

            $('#product-detail-tabs-content > .tab-pane').removeClass('active show')
            $($(target).attr('href')).addClass('active show')

            $('html, body').animate(
                {
                    scrollTop: ($(target).offset().top - $('.header--product .navigation').height() - 250) + 'px',
                },
                0,
            )
        })

        $(document).on('click', '#sticky-add-to-cart .add-to-cart-button', e => {
            e.preventDefault()
            e.stopPropagation()

            const $this = $(e.currentTarget)

            $this.addClass('button-loading')

            setTimeout(function() {
                let target = '.js-product-content .cart-form button[name=' + $this.prop('name') + '].add-to-cart-button'

                $(document).find(target).trigger('click')

                $this.removeClass('button-loading')
            }, 200)
        })
    })
})(jQuery)
