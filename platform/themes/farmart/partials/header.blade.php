<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=1" name="viewport"/>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {!! BaseHelper::googleFonts('https://fonts.googleapis.com/css2?family=' . urlencode(theme_option('primary_font', 'Muli')) . ':wght@400;600;700&display=swap') !!}

        <style>
            :root {
                --primary-font: '{{ theme_option('primary_font', 'Muli') }}', sans-serif;
                --primary-color: {{ theme_option('primary_color', '#fab528') }};
                --heading-color: {{ theme_option('heading_color', '#000') }};
                --text-color: {{ theme_option('text_color', '#000') }};
                --primary-button-color: {{ theme_option('primary_button_color', '#000') }};
                --top-header-background-color: {{ theme_option('top_header_background_color', '#f7f7f7') }};
                --middle-header-background-color: {{ theme_option('middle_header_background_color', '#fff') }};
                --bottom-header-background-color: {{ theme_option('bottom_header_background_color', '#fff') }};
                --header-text-color: {{ theme_option('header_text_color', '#000') }};
                --header-text-secondary-color: {{ BaseHelper::hexToRgba(theme_option('header_text_color', '#000'), 0.5) }};
                --header-deliver-color: {{ BaseHelper::hexToRgba(theme_option('header_deliver_color', '#000'), 0.15) }};
                --footer-text-color: {{ theme_option('footer_text_color', '#555') }};
                --footer-heading-color: {{ theme_option('footer_heading_color', '#555') }};
                --footer-hover-color: {{ theme_option('footer_hover_color', '#fab528') }};
                --footer-border-color: {{ theme_option('footer_border_color', '#dee2e6') }};
            }
        </style>

        @php
            Theme::asset()->remove('language-css');
            Theme::asset()->container('footer')->remove('language-public-js');
            Theme::asset()->container('footer')->remove('simple-slider-owl-carousel-css');
            Theme::asset()->container('footer')->remove('simple-slider-owl-carousel-js');
            Theme::asset()->container('footer')->remove('simple-slider-css');
            Theme::asset()->container('footer')->remove('simple-slider-js');
        @endphp

        {!! Theme::header() !!}
    </head>
    <body @if (BaseHelper::isRtlEnabled()) dir="rtl" @endif @if (Theme::get('bodyClass')) class="{{ Theme::get('bodyClass') }}" @endif>
        @if (theme_option('preloader_enabled', 'yes') == 'yes')
            {!! Theme::partial('preloader') !!}
        @endif

        {!! Theme::partial('svg-icons') !!}
        {!! apply_filters(THEME_FRONT_BODY, null) !!}

        <header class="header header-js-handler" data-sticky="{{ theme_option('sticky_header_enabled', 'yes') == 'yes' ? 'true' : 'false' }}">
            <div @class([
                'header-top d-none d-lg-block',
                'header-content-sticky' => theme_option('sticky_header_content_position', 'middle') == 'top',
            ])>
                <div class="container-xxxl">
                    <div class="header-wrapper">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <div class="header-info">
                                    {!! Menu::renderMenuLocation('header-navigation', ['view' => 'menu-default']) !!}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="header-info header-info-right">
                                    <ul>
                                        @if (is_plugin_active('language'))
                                            {!! Theme::partial('language-switcher') !!}
                                        @endif
                                        @if (is_plugin_active('ecommerce'))
                                            @if (count($currencies) > 1)
                                                <li>
                                                    <a class="language-dropdown-active" href="#">
                                                        <span>{{ get_application_currency()->title }}</span>
                                                        <span class="svg-icon">
                                                            <svg>
                                                                <use href="#svg-icon-chevron-down" xlink:href="#svg-icon-chevron-down"></use>
                                                            </svg>
                                                        </span>
                                                    </a>
                                                    <ul class="language-dropdown">
                                                        @foreach ($currencies as $currency)
                                                            @if ($currency->id !== get_application_currency_id())
                                                                <li>
                                                                    <a href="{{ route('public.change-currency', $currency->title) }}">
                                                                        <span>{{ $currency->title }}</span>
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endif
                                            @if (auth('customer')->check())
                                                <li>
                                                    <a href="{{ route('customer.overview') }}">{{ auth('customer')->user()->name }}</a> <span class="d-inline-block ms-1">(<a href="{{ route('customer.logout') }}" class="color-primary">{{ __('Logout') }}</a>)</span>
                                                </li>
                                            @else
                                                <li><a href="{{ route('customer.login') }}">{{ __('Login') }}</a></li>
                                                <li><a href="{{ route('customer.register') }}">{{ __('Register') }}</a></li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div @class([
                'header-middle',
                'header-content-sticky' => theme_option('sticky_header_content_position', 'middle') == 'middle',
            ])>
                <div class="container-xxxl">
                    <div class="header-wrapper">
                        <div class="header-items header__left">
                            @if (theme_option('logo'))
                                <div class="logo">
                                    <a href="{{ route('public.index') }}">
                                        <img src="{{ RvMedia::getImageUrl(theme_option('logo')) }}" alt="{{ theme_option('site_title') }}" />
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="header-items header__center">
                            @if (is_plugin_active('ecommerce'))
                                <form class="form--quick-search" action="{{ route('public.products') }}" data-ajax-url="{{ route('public.ajax.search-products') }}" method="get">
                                    <div class="form-group--icon" style="display: none">
                                        <div class="product-category-label">
                                            <span class="text">{{ __('All Categories') }}</span>
                                            <span class="svg-icon">
                                                <svg>
                                                    <use href="#svg-icon-chevron-down" xlink:href="#svg-icon-chevron-down"></use>
                                                </svg>
                                            </span>
                                        </div>
                                        <select class="form-control product-category-select" name="categories[]">
                                            <option value="0">{{ __('All Categories') }}</option>
                                            {!! Theme::partial('product-categories-select', ['categories' => $categories->toArray(), 'indent' => null]) !!}
                                        </select>
                                    </div>
                                    <input class="form-control input-search-product" name="q" type="text"
                                        placeholder="{{ __("I'm shopping for...") }}" autocomplete="off">
                                    <button class="btn" type="submit" aria-label="Submit">
                                        <span class="svg-icon">
                                            <svg>
                                                <use href="#svg-icon-search" xlink:href="#svg-icon-search"></use>
                                            </svg>
                                        </span>
                                    </button>
                                    <div class="panel--search-result"></div>
                                </form>
                            @endif
                        </div>
                        <div class="header-items header__right">
                            @if (theme_option('hotline'))
                                <div class="header__extra header-support">
                                    <div class="header-box-content">
                                        <span>{{ theme_option('hotline') }}</span>
                                        <p>{{ __('Support 24/7') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if (is_plugin_active('ecommerce'))
                                @if (EcommerceHelper::isCompareEnabled())
                                    <div class="header__extra header-compare">
                                        <a class="btn-compare" href="{{ route('public.compare') }}">
                                            <i class="icon-repeat"></i>
                                            <span class="header-item-counter">{{ Cart::instance('compare')->count() }}</span>
                                        </a>
                                    </div>
                                @endif
                                @if (EcommerceHelper::isWishlistEnabled())
                                    <div class="header__extra header-wishlist">
                                        <a class="btn-wishlist" href="{{ route('public.wishlist') }}">
                                            <span class="svg-icon">
                                                <svg>
                                                    <use href="#svg-icon-wishlist" xlink:href="#svg-icon-wishlist"></use>
                                                </svg>
                                            </span>
                                            <span class="header-item-counter">
                                                {{ auth('customer')->check() ? auth('customer')->user()->wishlist()->count() : Cart::instance('wishlist')->count() }}
                                            </span>
                                        </a>
                                    </div>
                                @endif
                                @if (EcommerceHelper::isCartEnabled())
                                    <div class="header__extra cart--mini" tabindex="0" role="button">
                                        <div class="header__extra">
                                            <a class="btn-shopping-cart" href="{{ route('public.cart') }}">
                                                <span class="svg-icon">
                                                    <svg>
                                                        <use href="#svg-icon-cart" xlink:href="#svg-icon-cart"></use>
                                                    </svg>
                                                </span>
                                                <span class="header-item-counter">{{ Cart::instance('cart')->count() }}</span>
                                            </a>
                                            <span class="cart-text">
                                                <span class="cart-title">{{ __('Your Cart') }}</span>
                                                <span class="cart-price-total">
                                                    <span class="cart-amount">
                                                        <bdi>
                                                            <span>{{ format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax()) }}</span>
                                                        </bdi>
                                                    </span>
                                                </span>
                                            </span>
                                        </div>
                                        <div class="cart__content" id="cart-mobile">
                                            <div class="backdrop"></div>
                                            <div class="mini-cart-content">
                                                <div class="widget-shopping-cart-content">
                                                    {!! Theme::partial('cart-mini.list') !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div @class([
                'header-bottom',
                'header-content-sticky' => theme_option('sticky_header_content_position', 'middle') == 'bottom',
            ])>
                <div class="header-wrapper">
                    <nav class="navigation">
                        <div class="container-xxxl">
                            <div class="navigation__left">
                                @if (is_plugin_active('ecommerce'))
                                    <div class="menu--product-categories">
                                        <div class="menu__toggle">
                                            <span class="svg-icon">
                                                <svg>
                                                    <use href="#svg-icon-list" xlink:href="#svg-icon-list"></use>
                                                </svg>
                                            </span>
                                            <span class="menu__toggle-title">{{ __('Shop by Category') }}</span>
                                        </div>
                                        <div class="menu__content">
                                            <ul class="menu--dropdown">
                                                @php
                                                    Theme::set('productCategoriesDropdown', Theme::partial('product-categories-dropdown', compact('categories')))
                                                @endphp
                                                {!! Theme::get('productCategoriesDropdown') !!}
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="navigation__center">
                                {!! Menu::renderMenuLocation('main-menu', [
                                    'view'    => 'menu',
                                    'options' => ['class' => 'menu'],
                                ]) !!}
                            </div>
                            <div class="navigation__right">
                                @if (is_plugin_active('ecommerce') && EcommerceHelper::isEnabledCustomerRecentlyViewedProducts())
                                    <div class="header-recently-viewed" data-url="{{ route('public.ajax.recently-viewed-products') }}" role="button">
                                        <h3 class="recently-title">
                                            <span class="svg-icon recent-icon">
                                                <svg>
                                                    <use href="#svg-icon-refresh" xlink:href="#svg-icon-refresh"></use>
                                                </svg>
                                            </span>
                                            {{ __('Recently Viewed') }}
                                        </h3>
                                        <div class="recently-viewed-inner container-xxxl">
                                            <div class="recently-viewed-content">
                                                <div class="loading--wrapper">
                                                    <div class="loading"></div>
                                                </div>
                                                <div class="recently-viewed-products"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
            <div class="header-mobile header-js-handler" data-sticky="{{ theme_option('sticky_header_mobile_enabled', 'yes') == 'yes' ? 'true' : 'false' }}">
                <div class="header-items-mobile header-items-mobile--left">
                    <div class="menu-mobile">
                        <div class="menu-box-title">
                            <div class="icon menu-icon toggle--sidebar" href="#menu-mobile">
                                <span class="svg-icon">
                                    <svg>
                                        <use href="#svg-icon-list" xlink:href="#svg-icon-list"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="header-items-mobile header-items-mobile--center">
                    @if (theme_option('logo'))
                        <div class="logo">
                            <a href="{{ route('public.index') }}">
                                <img src="{{ RvMedia::getImageUrl(theme_option('logo')) }}" alt="{{ theme_option('site_title') }}" width="155" />
                            </a>
                        </div>
                    @endif
                </div>
                <div class="header-items-mobile header-items-mobile--right">
                    <div class="search-form--mobile search-form--mobile-right search-panel">
                        <a class="open-search-panel toggle--sidebar" href="#search-mobile" title="{{ __('Search') }}">
                            <span class="svg-icon">
                                <svg>
                                    <use href="#svg-icon-search" xlink:href="#svg-icon-search"></use>
                                </svg>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </header>
