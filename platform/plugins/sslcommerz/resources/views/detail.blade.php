@if ($payment && $data = Arr::get($payment, 'element.0', []))
    <hr>
    @if (Arr::get($data, 'tran_id'))
        <p>{{ trans('plugins/payment::payment.payment_id') }}: {{ Arr::get($data, 'tran_id') }}</p>
    @endif
    @if (Arr::get($data, 'currency_amount'))
        <p>{{ trans('plugins/payment::payment.amount') }}: {{ Arr::get($data, 'currency_amount') }} {{ Arr::get($data, 'currency_type') }}</p>
    @endif
    @if (Arr::get($data, 'status'))
        <p>{{ trans('plugins/payment::payment.status') }}: {{ Arr::get($data, 'status') }}</p>
    @endif
    @if (Arr::get($data, 'card_issuer'))
        <p>{{ trans('plugins/payment::payment.card') }}: {{ Arr::get($data, 'card_issuer') }}</p>
    @endif
    @if (Arr::get($data, 'card_issuer_country'))
        <p>{{ trans('plugins/payment::payment.country') }}: {{ Arr::get($data, 'card_issuer_country') }}</p>
    @endif

    @if (Arr::get($data, 'tran_date'))
        <p>{{ trans('core/base::tables.created_at') }}: {{ Carbon\Carbon::now()->parse(Arr::get($data, 'tran_date')) }}</p>
        <hr>
    @endif
    @if ($refunds = Arr::get($paymentModel->metadata, 'refunds', []))
        <h6 class="alert-heading">{{ trans('plugins/payment::payment.amount_refunded') }}:
            {{ collect($refunds)->sum('_data_request.refund_amount') }} {{ $paymentModel->currency }}</h6>
        @foreach ($refunds as $refund)
            <div id="{{ Arr::get($refund, 'refund_ref_id') }}">
                @include('plugins/sslcommerz::refund-detail')
            </div>
        @endforeach
    @endif
    @include('plugins/payment::partials.view-payment-source')
@endif
