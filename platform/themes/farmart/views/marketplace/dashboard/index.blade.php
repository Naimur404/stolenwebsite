@extends(MarketplaceHelper::viewPath('dashboard.layouts.master'))

@section('content')
    @if ($totalProducts)
        <div class="mb-1 text-end">
            <button class="select-date-range-btn date-range-picker"
                    data-format-value="{{ trans('plugins/ecommerce::reports.date_range_format_value', ['from' => '__from__', 'to' => '__to__']) }}"
                    data-format="{{ Str::upper(config('core.base.general.date_format.js.date')) }}"
                    data-href="{{ route('marketplace.vendor.dashboard') }}"
                    data-start-date="{{ $data['startDate'] }}"
                    data-end-date="{{ $data['endDate'] }}">
                <i class="fa fa-calendar me-1"></i>
                <span>{{ trans('plugins/ecommerce::reports.date_range_format_value', [
                        'from' => $data['startDate']->translatedFormat('Y-m-d'),
                        'to'   => $data['endDate']->translatedFormat('Y-m-d')
                    ]) }}</span>
            </button>
        </div>
    @endif
    <section class="ps-dashboard report-chart-content" id="report-chart">
        @include(MarketplaceHelper::viewPath('dashboard.partials.dashboard-content'))
    </section>
@stop

@push('footer')
    <script>
        'use strict';

        var BotbleVariables = BotbleVariables || {};
        BotbleVariables.languages = BotbleVariables.languages || {};
        BotbleVariables.languages.reports = {!! json_encode(trans('plugins/ecommerce::reports.ranges'), JSON_HEX_APOS) !!}
    </script>
@endpush
