<header class="header--mobile">
    <div class="header__left">
        <button class="ps-drawer-toggle"><i class="icon-menu"></i></button>
    </div>
    <div class="header__center">
        <a class="ps-logo" href="{{ route('marketplace.vendor.dashboard') }}">
            @php $logo = theme_option('logo_vendor_dashboard', theme_option('logo')); @endphp
            @if ($logo)
                <img src="{{ RvMedia::getImageUrl($logo) }}" alt="{{ theme_option('site_title') }}">
            @endif
        </a>
    </div>
    <div class="header__right"><a class="header__site-link" href="{{ route('customer.logout') }}"><i class="icon-exit-right"></i></a></div>
</header>
<aside class="ps-drawer--mobile">
    <div class="ps-drawer__header">
        <h4>Menu</h4>
        <button class="ps-drawer__close"><i class="icon-cross"></i></button>
    </div>
    <div class="ps-drawer__content">
        @include(MarketplaceHelper::viewPath('dashboard.layouts.menu'))
    </div>
</aside>
<div class="ps-site-overlay"></div>
<main class="ps-main">
    <div class="ps-main__sidebar">
        <div class="ps-sidebar">
            <div class="ps-sidebar__top">
                <div class="ps-block--user-wellcome">
                    <div class="ps-block__left">
                        <img src="{{ auth('customer')->user()->store->logo_url }}" alt="{{ auth('customer')->user()->store->name }}" width="80" />
                    </div>
                    <div class="ps-block__right">
                        <p>{{ __('Hello') }}, {{ auth('customer')->user()->name }}</p>
                        <small>{{ __('Joined on :date', ['date' => auth('customer')->user()->created_at->translatedFormat('M d, Y')]) }}</small>
                    </div>
                    <div class="ps-block__action"><a href="{{ route('customer.logout') }}"><i class="icon-exit"></i></a></div>
                </div>
                <div class="ps-block--earning-count"><small>{{ __('Earnings') }}</small>
                    <h3>{{ format_price(auth('customer')->user()->balance) }}</h3>
                </div>
            </div>
            <div class="ps-sidebar__content">
                <div class="ps-sidebar__center">
                    @include(MarketplaceHelper::viewPath('dashboard.layouts.menu'))
                </div>
                <div class="ps-sidebar__footer">
                    <div class="ps-copyright">
                        @php $logo = theme_option('logo_vendor_dashboard', theme_option('logo')); @endphp
                        @if ($logo)
                            <img src="{{ RvMedia::getImageUrl($logo)}}" alt="{{ theme_option('site_title') }}" height="40">
                        @endif
                        <p>{{ theme_option('copyright') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="ps-main__wrapper" id="vendor-dashboard">
        <header class="header--dashboard">
            <div class="header__left">
                <h3>{{ page_title()->getTitle(false) }}</h3>
            </div>
            @if (auth('customer')->user()->store && auth('customer')->user()->store->id)
                <div class="header__right">
                    @if (is_plugin_active('language'))
                        <div class="me-4">
                            <ul class="mb-0">
                                @include(MarketplaceHelper::viewPath('dashboard.partials.language-switcher'))
                            </ul>
                        </div>
                    @endif
                    <a class="header__site-link ms-2" href="{{ auth('customer')->user()->store->url }}" target="_blank"><span>{{ __('View your store') }}</span><i class="icon-exit-right"></i></a>
                </div>
            @endif
        </header>

        <div id="main">
            <div id="app">
                @yield('content')
            </div>
        </div>
    </div>
</main>
