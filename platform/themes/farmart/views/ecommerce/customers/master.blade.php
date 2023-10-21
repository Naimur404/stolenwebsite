@php
    Theme::layout('full-width');
    Theme::asset()->container('footer')->remove('ecommerce-utilities-js');
@endphp
{!! Theme::partial('page-header', ['size' => 'xxxl']) !!}

@php
    $menus = [
        'customer.overview' => [
            'label' => __('Overview'),
        ],
        'customer.edit-account' => [
            'label' => __('Profile'),
        ],
        'customer.orders' => [
            'label'  => __('Orders'),
            'routes' => ['customer.orders.view']
        ],
        'customer.product-reviews' => [
            'label'  => __('Product Reviews'),
            'routes' => ['customer.product-reviews']
        ],
        'customer.downloads' => [
            'label' => __('Downloads'),
        ],
        'customer.order_returns' => [
            'label'  => __('Order Return Requests'),
            'routes' => ['customer.order_returns', 'customer.order_returns.detail']
        ],
        'customer.address' => [
            'label'  => __('Addresses'),
            'routes' => [
                'customer.address.create',
                'customer.address.edit'
            ]
        ],
        'customer.change-password' => [
            'label' => __('Change password'),
        ],
    ];

    $routeName = Route::currentRouteName();

    if (! EcommerceHelper::isEnabledSupportDigitalProducts()) {
        unset($menus['customer.downloads']);
    }

    if (! EcommerceHelper::isReviewEnabled()) {
        unset($menus['customer.product-reviews']);
    }

    if (! EcommerceHelper::isOrderReturnEnabled()) {
        unset($menus['customer.order_returns']);
    }
@endphp
<div class="container-xxxl">
    <div class="row my-4">
        <div class="col-md-3">
            <ul class="nav flex-column dashboard-navigation">
                @foreach ($menus as $key => $item)
                    <li class="nav-item">
                        <a class="nav-link
                            @if ($routeName == $key
                            || in_array($routeName, Arr::get($item, 'routes', []))) active @endif"
                            aria-current="@if ($routeName == $key
                            || in_array($routeName, Arr::get($item, 'routes', []))) true @else false @endif"
                            href="{{ route($key) }}">{{ Arr::get($item, 'label') }}</a>
                    </li>
                @endforeach
                @if (is_plugin_active('marketplace'))
                    @if (auth('customer')->user()->is_vendor)
                        <li class="nav-item">
                            <a class="nav-link" aria-current="false" href="{{ route('marketplace.vendor.dashboard') }}">{{ __('Vendor dashboard') }}</a>
                        </li>
                    @else
                        <li class="nav-item @if ($routeName == 'marketplace.vendor.become-vendor') active @endif">
                            <a class="nav-link" aria-current="@if ($routeName == 'marketplace.vendor.become-vendor') active @endif"
                                href="{{ route('marketplace.vendor.become-vendor') }}">{{ __('Become a vendor') }}</a>
                        </li>
                    @endif
                @endif
                <li class="nav-item">
                    <a class="nav-link" aria-current="false" href="{{ route('customer.logout') }}">{{ __('Logout') }}</a>
                </li>
            </ul>
        </div>
        <div class="col-md-9">
            <div class="customer-dashboard-container">
                <div class="customer-dashboard-content">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>
