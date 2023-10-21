<div class="mx-0 bg-white row report-chart-content pt-3 mb-3" id="report-chart">
    <div class="row">
        <div class="col-md-8 mb-2">
            <div class="rp-card rp-card-sale-report">
                <div class="rp-card-header">
                    <h5>{{ trans('plugins/marketplace::marketplace.reports.sale_commissions') }}</h5>
                </div>

                <div class="rp-card__content">
                    <div id="sale-commissions-chart"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="rp-card rp-card-earning">
                <div class="rp-card-header">
                    <h5>{{ trans('plugins/ecommerce::reports.earnings') }}</h5>
                </div>
                <div class="rp-card-content">
                    @if ($count['revenues']->count())
                        <div class="rp-card-chart position-relative mb-3">
                            <div id="revenue-earnings-chart"></div>
                            <div class="rp-card-information">
                                <i class="fas fa-wallet"></i>
                                <strong>{{ format_price($salesReport['totalFee']) }}</strong>
                                <small>{{ trans('plugins/ecommerce::reports.total_earnings') }}</small>
                            </div>
                        </div>
                        <div class="rp-card-status text-center">
                            @foreach ($count['revenues'] as $item)
                                <p>
                                    <small>
                                        <i class="fas fa-circle me-2" style="color: {{ Arr::get($item, 'color') }}"></i>
                                    </small>
                                    <strong>{{ format_price($item['value']) }}</strong>
                                    <span>{{ $item['label'] }}</span>
                                </p>
                            @endforeach
                        </div>
                    @else
                        <div>
                            @include('core/dashboard::partials.no-data')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $revenues = fn (string $key): array => $count['revenues']->pluck($key)->toArray();
@endphp

@if (request()->ajax())
    @include('plugins/marketplace::reports.widgets.chart-script')
@else
    @push('footer')
        @include('plugins/marketplace::reports.widgets.chart-script')
    @endpush
@endif
