<div class="note note-info" role="alert">
    <p class="mb-2 uppercase"><strong>{{ $title ?? __('You will receive money through the information below') }}</strong>:</p>
    @foreach (\Botble\Marketplace\Enums\PayoutPaymentMethodsEnum::getFields($paymentChannel) as $key => $field)
        @if (Arr::get($bankInfo, $key))
            <p>{{ Arr::get($field, 'title') }}: <strong>{{ Arr::get($bankInfo, $key) }}</strong></p>
        @endif
    @endforeach

    @isset($link)
        <p>{!! BaseHelper::clean(__('You can change it <a href=":link">here</a>', ['link' => $link])) !!}.</p>
    @endisset

    @if ($taxInfo && (Arr::get($taxInfo, 'business_name') || Arr::get($taxInfo, 'tax_id') || Arr::get($taxInfo, 'address')))
        <br>
        <p class="mb-2 uppercase"><strong>{{ __('Tax info') }}</strong>:</p>
        @if (Arr::get($taxInfo, 'business_name'))
            <p>{{ __('Business Name') }}: <strong>{{ Arr::get($taxInfo, 'business_name') }}</strong></p>
        @endif

        @if (Arr::get($taxInfo, 'tax_id'))
            <p>{{ __('Tax ID') }}: <strong>{{ Arr::get($taxInfo, 'tax_id') }}</strong></p>
        @endif

        @if (Arr::get($taxInfo, 'address'))
            <p>{{ __('Address') }}: <strong>{{ Arr::get($taxInfo, 'address') }}</strong></p>
        @endif
    @endif
</div>
