@if ($displayBasePrice)
    <small style="display:block">{{ trans('plugins/ecommerce::product-option.price') }}: <strong style="float: right">{{ format_price($product->original_product->front_sale_price_with_taxes) }}</strong></small>
@endif

@foreach ($productOptions['optionCartValue'] as $key => $optionValue)
    @php
        $price = 0;
        $totalOptionValue = count($optionValue);
    @endphp
    @continue(!$totalOptionValue)
    <small style="display: block">
        {{ $productOptions['optionInfo'][$key] }}:

        @foreach ($optionValue as $value)
            @php
                if ($value['affect_type'] == 1) {
                    $price += ($product->original_product->front_sale_price_with_taxes * $value['affect_price']) / 100;
                } else {
                    $price += $value['affect_price'];
                }
            @endphp

            <strong>{{ $value['option_value'] }}</strong>
            @if ($key + 1 < $totalOptionValue) , @endif
        @endforeach

        @if ($price > 0)
            <strong style="float: right">+ {{ format_price($price) }}</strong>
        @endif
    </small>
@endforeach
