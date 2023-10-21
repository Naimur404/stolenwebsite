@if ($displayBasePrice && $basePrice != null)
    <div class="small d-flex justify-content-between">
        <span>{{ trans('plugins/ecommerce::product-option.price') }}:</span>
        <strong>{{ format_price($basePrice) }}</strong>
    </div>
@endif

@foreach ($productOptions['optionCartValue'] as $key => $optionValue)
    @php
        $price = 0;
        $totalOptionValue = count($optionValue);
    @endphp
    @continue(! $totalOptionValue)
    <div class="small d-flex justify-content-between">
        <span>
            {{ $productOptions['optionInfo'][$key] }}:
            @foreach ($optionValue as $value)
                @php
                    if (Arr::get($value, 'option_type') != 'field') {
                        if ($value['affect_type'] == 1) {
                            $price += ($basePrice * $value['affect_price']) / 100;
                        } else {
                            $price += $value['affect_price'];
                        }
                    }
                @endphp
                <strong>{{ $value['option_value'] }}</strong>
                @if ($key + 1 < $totalOptionValue) , @endif
            @endforeach
        </span>
        @if ($price > 0)
            <strong>+ {{ format_price($price) }}</strong>
        @endif
    </div>
@endforeach
