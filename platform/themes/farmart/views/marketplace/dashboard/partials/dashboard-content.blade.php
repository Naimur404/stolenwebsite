<div class="ps-section__left">
    <div class="row">
        @if (!$totalProducts)
            <div class="col-12">
                <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                    <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </symbol>
                </svg>
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use xlink:href="#check-circle-fill"/></svg>
                        {{ __('Congratulations on being a vendor at :site_title', ['site_title' => theme_option('site_title')]) }}
                    </h4>
                    <p>{{ __('Attract your customers with the best products.') }}</p>
                    <hr>
                    <p class="mb-0">{!! __('Create a new product <a href=":url">here</a>', ['url' => route('marketplace.vendor.products.create')]) !!}</p>
                </div>
            </div>
        @elseif (!$totalOrders)
            <div class="col-12">
                <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                    <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                      </symbol>
                </svg>
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use xlink:href="#info-fill"/></svg>
                        {{ __('You have :total product(s) but no orders yet', ['total' => $totalProducts]) }}
                    </h4>
                    <hr>
                    <p class="mb-0">{!! __('View your store <a href=":url">here</a>', ['url' => $user->store->url]) !!}</p>
                </div>
            </div>
        @else
            <div class="col-md-8">
                <div class="ps-card ps-card--sale-report">
                    <div class="ps-card__header">
                        <h4>{{ __('Sales Reports') }}</h4>
                        <a href="{{ route('marketplace.vendor.revenues.index') }}">
                            <small>{{ __('Revenues in :label', ['label' => $data['predefinedRange']]) }} <i class="fas fa-angle-double-right"></i></small>
                        </a>
                    </div>
                    <div class="ps-card__content">
                        <div id='sales-report-chart'>
                            <sales-reports-chart url="{{ route('marketplace.vendor.chart.month') }}" date_from='{{ $data['startDate']->format('Y-m-d') }}' date_to='{{ $data['endDate']->format('Y-m-d') }}'></sales-reports-chart>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="ps-card ps-card--earning">
                    <div class="ps-card__header">
                        <h4>{{ __('Earnings') }}</h4>
                        <div class="text-primary">
                            <small>{{ __('Earnings in :label', ['label' => $data['predefinedRange']]) }}</small>
                        </div>
                    </div>
                    <div class="ps-card__content">
                        <div class="ps-card__chart">
                            <div id='revenue-chart'>
                                <revenue-chart :data="{{ json_encode([
                                ['label' => __('Revenue'), 'value' => $data['revenue']['amount'], 'color' => '#80bc00'],
                                ['label' => __('Fees'), 'value' => $data['revenue']['fee'], 'color' => '#fcb800'],
                                ['label' => __('Withdrawals'), 'value' => $data['revenue']['withdrawal'], 'color' => '#fc6b00']]) }}"></revenue-chart>
                            </div>
                            <div class="ps-card__information">
                                <i class="icon-wallet"></i>
                                <strong>{{ format_price($data['revenue']['sub_amount']) }}</strong>
                                <small>{{ __('Balance') }}</small>
                            </div>
                        </div>
                        <div class="ps-card__status">
                            <p class="green">
                                <strong>{{ format_price($data['revenue']['amount']) }}</strong><span>{{ __('Revenue') }}</span>
                            </p>
                            <p class="red">
                                <strong>{{ format_price($data['revenue']['withdrawal']) }}</strong><span data-bs-toggle="tooltip" data-bs-original-title="{{ __('Includes Completed, Pending, and Processing statuses') }}">{{ __('Withdrawals') }}</span>
                            </p>
                            <p class="yellow">
                                <strong>{{ format_price($data['revenue']['fee']) }}</strong><span>{{ __('Fees') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($totalOrders)
        <div class="ps-card">
            <div class="ps-card__header">
                <h4>{{ __('Recent Orders') }}</h4>
            </div>
            <div class="ps-card__content">
                <div class="table-responsive">
                    <table class="table ps-table">
                        <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Payment') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Total') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['orders'] as $order)
                                <tr>
                                    <td><a href="{{ route('marketplace.vendor.orders.edit', $order->id) }}">{{ get_order_code($order->id) }}</a></td>
                                    <td><strong>{{ $order->created_at->translatedFormat('M d, Y') }}</strong></td>
                                    <td><a href="{{ route('marketplace.vendor.orders.edit', $order->id) }}"><strong>{{ $order->user->name ?: $order->address->name }}</strong></a></td>
                                    <td>{!! BaseHelper::clean($order->payment->status->toHtml()) !!}</td>
                                    <td>{!! BaseHelper::clean($order->status->toHtml()) !!}</td>
                                    <td><strong>{{ format_price($order->amount) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('No orders!') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="ps-card__footer"><a class="ps-card__morelink" href="{{ route('marketplace.vendor.orders.index') }}">{{ __('View Full Orders') }}<i class="icon icon-chevron-right"></i></a></div>
        </div>
    @endif

    @if ($totalProducts)
        <hr>
        <div class="ps-card">
            <div class="ps-card__header">
                <h4>{{ __('Top Selling Products') }}</h4>
            </div>
            <div class="ps-card__content">
                <div class="table-responsive">
                    <table class="table ps-table">
                        <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Created at') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['products'] as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td><a href="{{ route('marketplace.vendor.products.edit', $product->id) }}"><strong>{{ $product->name }}</strong></a></td>
                                    <td><strong>{!! BaseHelper::clean($product->price_in_table) !!}</strong></td>
                                    <td>{!! BaseHelper::clean($product->status->toHtml()) !!}</td>
                                    <td><strong>{{ $product->created_at->translatedFormat('M d, Y') }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('No products!') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="ps-card__footer">
                <a class="ps-card__morelink" href="{{ route('marketplace.vendor.products.index') }}">
                    {{ __('View Full Products') }}<i class="icon icon-chevron-right"></i>
                </a>
            </div>
        </div>
    @endif
</div>
<div class="ps-section__right">
    <section class="ps-card ps-card--statics">
        <div class="ps-card__header d-block">
            <h4>{{ __('Statistics') }}</h4>
            <div class="text-primary">
                <small>{{ __('Statistics in :label', ['label' => $data['predefinedRange']]) }}</small>
            </div>
        </div>
        <div class="ps-card__content">
            <div class="ps-block--stat yellow">
                <div class="ps-block__left"><span><i class="icon-bag2"></i></span></div>
                <div class="ps-block__content">
                    <p>{{ __('Orders') }}</p>
                    <h4>{{ $data['orders']->count() }}</h4>
                </div>
            </div>
            <div class="ps-block--stat pink">
                <div class="ps-block__left"><span><i class="icon-bag-dollar"></i></span></div>
                <div class="ps-block__content">
                    <p>{{ __('Revenue') }}</p>
                    <h4>{{ format_price($data['revenue']['amount']) }}</h4>
                </div>
            </div>
            <div class="ps-block--stat green">
                <div class="ps-block__left"><span><i class="icon-database"></i></span></div>
                <div class="ps-block__content">
                    <p>{{ __('Products') }}</p>
                    <h4>{{ $data['products']->count() }}</h4>
                </div>
            </div>
        </div>
    </section>
</div>
