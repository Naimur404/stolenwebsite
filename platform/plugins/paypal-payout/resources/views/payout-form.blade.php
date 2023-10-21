@if ($data->canEditStatus() && ! $data->transaction_id)
    <a href="{{ route('paypal-payout.make', $data->id) }}" class="btn btn-warning btn-payout-button"><i class="fab fa-paypal"></i> {{ __('Process payout') }}</a>
@else
    <div id="payout-transaction-detail" data-url="{{ route('paypal-payout.retrieve', $data->transaction_id) }}">
        @include('core/base::elements.loading')
    </div>
@endif
