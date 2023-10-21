@php
    $menus = collect([
        [
            'key'   => 'marketplace.vendor.dashboard',
            'icon'  => 'icon material-icons md-home',
            'name'  => __('Dashboard'),
            'order' => 1,
        ],
        [
            'key'    => 'marketplace.vendor.products.index',
            'icon'   => 'icon material-icons md-shopping_bag',
            'name'   => __('Products'),
            'routes' => [
                'marketplace.vendor.products.create',
                'marketplace.vendor.products.edit',
            ],
            'order' => 2,
        ],
        [
            'key'    => 'marketplace.vendor.orders.index',
            'icon'   => 'icon material-icons md-shopping_cart',
            'name'   => __('Orders'),
            'routes' => [
                'marketplace.vendor.orders.edit',
            ],
            'order' => 3,
        ],
        [
            'key'    => 'marketplace.vendor.discounts.index',
            'icon'   => 'icon material-icons md-card_giftcard',
            'name'   => __('Coupons'),
            'routes' => [
                'marketplace.vendor.discounts.create',
                'marketplace.vendor.discounts.edit',
            ],
            'order' => 4,
        ],
        [
            'key'    => 'marketplace.vendor.withdrawals.index',
            'icon'   => 'icon material-icons md-money',
            'name'   => __('Withdrawals'),
            'routes' => [
                'marketplace.vendor.withdrawals.create',
                'marketplace.vendor.withdrawals.edit',
            ],
            'order' => 5,
        ],
        [
            'key'    => 'marketplace.vendor.statements.index',
            'icon'   => 'icon material-icons md-money',
            'name'   => __('Statements'),
            'routes' => [],
            'order' => 6,
        ],
        [
            'key'   => 'marketplace.vendor.settings',
            'icon'  => 'icon material-icons md-settings',
            'name'  => __('Settings'),
            'order' => 7,
        ],
        [
            'key'   => 'customer.overview',
            'icon'  => 'icon material-icons md-person',
            'name'  => __('Customer dashboard'),
            'order' => 8,
        ],
    ]);

    if (EcommerceHelper::isReviewEnabled()) {
        $menus->push([
            'key'   => 'marketplace.vendor.reviews.index',
            'icon'  => 'icon material-icons md-comment',
            'name'  => __('Reviews'),
            'order' => 6,
        ]);
    }

    if (EcommerceHelper::isOrderReturnEnabled()) {
        $menus->push([
            'key'    => 'marketplace.vendor.order-returns.index',
            'icon'   => 'icon material-icons md-assignment_returned',
            'name'   => __('Order Returns'),
            'routes' => [
                'marketplace.vendor.order-returns.edit',
            ],
            'order' => 3,
        ],);
    }

    if (MarketplaceHelper::allowVendorManageShipping()) {
        $menus->push([
            'key'    => 'marketplace.vendor.shipments.index',
            'icon'   => 'icon material-icons md-local_shipping',
            'name'   => __('Shipments'),
            'routes' => [
                'marketplace.vendor.shipments.edit',
            ],
            'order' => 3,
        ],);
    }

    $currentRouteName = Route::currentRouteName();
@endphp

<nav>
    <ul class="menu-aside">
        @foreach ($menus->sortBy('order') as $item)
            <li class="menu-item @if ($currentRouteName == $item['key'] || in_array($currentRouteName, Arr::get($item, 'routes', []))) active @endif">
                <a class="menu-link" href="{{ route($item['key']) }}">
                    <i class="{{ $item['icon'] }}"></i>
                    <span class="text">{{ $item['name'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
    <br />
    <br />
</nav>
