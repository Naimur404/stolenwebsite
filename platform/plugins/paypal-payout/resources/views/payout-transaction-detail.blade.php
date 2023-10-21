<div class="note note-success mb-0" role="alert">
    <p class="mb-2 uppercase"><strong>{{ __('PayPal payout info') }}</strong>:</p>
    <p>{{ __('Transaction ID') }}: <strong>{{ $transactionId }}</strong></p>
    <p>{{ __('Status') }}: <strong>{{ $status }}</strong></p>
    <p>{{ __('Amount') }}: <strong>{{ $amount }}</strong></p>
    <p>{{ __('Fee') }}: <strong>{{ $fee }}</strong></p>
    <p>{{ __('Created At') }}: <strong>{{ $createdAt }}</strong></p>
    <p>{{ __('Completed At') }}: <strong>{{ $completedAt }}</strong></p>
    <p>{{ __('Funding Source') }}: <strong>{{ $fundingSource }}</strong></p>
</div>
