<div class="customer-order-detail">
    <div class="row">
        <div class="col-md-6">
            <h5>{{ __('Order information') }}</h5>
            <p>
                <span>{{ __('Order number') }}: </span>
                <strong>{{ $order->code }}</strong>
            </p>
            <p>
                <span>{{ __('Time') }}: </span>
                <strong>{{ $order->created_at->format('h:m d/m/Y') }}</strong>
            </p>
        </div>
        <div class="col-md-6">
            <p>
                <span>{{ __('Completed at') }}: </span>
                <strong class="text-info">{{ $order->completed_at->format('h:m d/m/Y') }}</strong>
            </p>
            @if ($order->shipment && $order->shipment->id)
                <p>
                    <span>{{ __('Shipment Status') }}: </span>
                    <strong class="text-info">{{ $order->shipment->status->label() }}</strong>
                </p>
            @endif
            @if (is_plugin_active('payment'))
                <p>
                    <span>{{ __('Payment status') }}: </span>
                    <strong class="text-info">{{ $order->payment->status->label() }}</strong>
                </p>
            @endif
        </div>
    </div>
    <br/>
    {!! Form::open(['url' => route('customer.order_returns.send_request'), 'method' => 'POST']) !!}
        {!! Form::hidden('order_id', $order->id) !!}

        @if (! EcommerceHelper::allowPartialReturn())
            <div class="col-sm-6 col-md-3 form-group">
                <label for="reason" class="col-form-label">
                    <strong>{{ __('Return Reason') }}:</strong>
                </label>
                {!! Form::select('reason', \Botble\Ecommerce\Enums\OrderReturnReasonEnum::labels(), old('reason'), [
                        'class' => 'form-control form-select',
                        'placeholder' => __('Choose Reason'),
                    ]) !!}
            </div>
            <br/>
        @endif

        <h5>{{ __('Choose products') }}</h5>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">{{ __('Image') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Price') }}</th>
                                <th class="text-center">{{ __('Quantity') }}</th>
                                @if (EcommerceHelper::allowPartialReturn())
                                    <th class="text-center">
                                        <label class="required">{{ __('Reason') }}</label>
                                    </th>
                                @endif
                                <th>{{ __('Refund amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalRefundAmount = $order->amount - $order->shipping_amount;
                                $totalPriceProducts = $order->products->sum(function ($item) {
                                    return $item->total_price_with_tax;
                                });
                                $ratio = $totalRefundAmount <= 0 ? 0 : $totalPriceProducts / $totalRefundAmount;
                            @endphp

                            @foreach ($order->products as $key => $orderProduct)
                                <tr>
                                    <td class="text-center">
                                        <div class="form-check">
                                            {!! Form::checkbox('return_items[' . $key . '][is_return]', $orderProduct->id, old('return_items.' . $key . '.is_return', true), [
                                                'class' => 'form-check-input'
                                            ]) !!}
                                        </div>
                                        <input type="hidden" name="return_items[{{ $key }}][order_item_id]" value="{{ $orderProduct->id }}">
                                    </td>
                                    <td class="text-center">
                                        <img src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                            alt="{{ $orderProduct->product_name }}" width="50">
                                    </td>
                                    <td>
                                        <span>{{ $orderProduct->product_name }}</span>
                                        @if ($sku = Arr::get($orderProduct->options, 'sku'))
                                            <span>{{ $sku }}</span>
                                        @endif
                                        @if ($attributes = Arr::get($orderProduct->options, 'attributes'))
                                            <p>
                                                <small>{{ $attributes }}</small>
                                            </p>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span>{{ format_price($orderProduct->price_with_tax) }}</span>
                                    </td>
                                    @if (EcommerceHelper::allowPartialReturn())
                                        <td class="product-quantity product-md d-md-table-cell d-block" data-title="Quantity">
                                            @php
                                                $qtyChooses = [];
                                                for ($i = 1; $i <= $orderProduct->qty; $i++) {
                                                    $qtyChooses[$i] = [
                                                        'data-qty' => $i,
                                                        'data-amount' => format_price($ratio == 0 ? 0 : ($orderProduct->price_with_tax * $i / $ratio)),
                                                    ];
                                                }
                                            @endphp
                                            <div class="form-group">
                                                {!! Form::select('return_items[' . $key . '][qty]', collect($qtyChooses)->pluck('data-qty', 'data-qty'), old('return_items.' . $key . '.qty', $orderProduct->qty), [
                                                    'class' => 'form-select select-return-item-qty'
                                                ], $qtyChooses) !!}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            {!! Form::select('return_items[' . $key . '][reason]', \Botble\Ecommerce\Enums\OrderReturnReasonEnum::labels(), old('return_items.' . $key . '.reason'), [
                                                'class' => 'form-control form-select',
                                                'placeholder' => __('Choose Reason'),
                                            ]) !!}
                                        </td>
                                    @else
                                        <td class="text-center">
                                            x {{ $orderProduct->qty }}
                                        </td>
                                    @endif
                                    <td class="text-center">
                                        <span class="return-amount">{{ format_price($ratio == 0 ? 0 : ($orderProduct->total_price_with_tax / $ratio)) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <br/>

            <div class="col-md-12 pt-3">
                @if ($order->canBeReturned())
                    {!! Form::submit(__('Submit Return Request'), ['class' => 'btn btn-lg btn-danger']) !!}
                @endif
            </div>
        </div>
    {!! Form::close() !!}
</div>
