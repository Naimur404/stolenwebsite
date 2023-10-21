<div class="list-group-item">
    {!! Form::input('radio', 'shipping_option', Arr::get($item, 'servicelevel.token'), array_merge($attributes, [
            'class' => 'magic-radio',
            'id'    => 'shipping-method-shippo-' . $index
        ])) !!}
    <label for="shipping-method-shippo-{{ $index }}">
        <div>
            @if ($image = Arr::get($item, 'provider_image_75'))
                <img src="{{ $image }}" alt="{{ Arr::get($item, 'servicelevel.name') }}" style="max-height: 40px; max-width: 55px">
            @endif
            <span>
                {{ Arr::get($item, 'servicelevel.name') }} - 
                {{ format_price($item['price']) }}
            </span>
            @if ($item['price'] != $order->shipping_amount && $deviant = $order->shipping_amount - $item['price'])
                <small class="{{ $deviant > 0 ? 'text-success' : 'text-warning' }}">
                    (<span>{{ $deviant > 0 ? '-' : '+' }}</span><span>{{ format_price($deviant) }}</span>)
                </small>
            @endif
        </div>
        @if ($days = Arr::get($item, 'days', Arr::get($item, 'estimated_days', 0)))
            <div>
                <small class="text-secondary">{{ trans('plugins/shippo::shippo.estimated_days', ['day' => $days]) }}</small>
            </div>
        @endif
    </label>
</div>
