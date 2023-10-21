<div class="panel panel-default">
    <div class="wrapper-content">
        <div class="clearfix">
            <div class="table-wrapper p-none">
                <table class="order-totals-summary">
                    <tbody>
                        @foreach ($shipment->order->products as $orderProduct)
                            @php
                                $product = $orderProduct->product->original_product;
                            @endphp
                            <tr class="border-bottom">
                                <td class="order-border text-center p-small">
                                    <i class="fa fa-truck"></i>
                                </td>
                                <td class="order-border p-small">
                                    <div class="flexbox-grid-default pl5 p-r5" style="align-items: center">
                                        <div class="flexbox-auto-50">
                                            <div class="wrap-img">
                                                <img class="thumb-image thumb-image-cartorderlist" src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}" />
                                            </div>
                                        </div>
                                        <div class="flexbox-content">
                                            <div>
                                                <a class="wordwrap hide-print" title="{{ $orderProduct->product_name }}"
                                                    href="{{ $productEditRouteName && $product && $product->id ? route($productEditRouteName, $product->id) : '#' }}">{{ $orderProduct->product_name }}</a>
                                                <p class="mb-0">
                                                    <small>{{ Arr::get($orderProduct->options, 'attributes', '') }}</small>
                                                </p>
                                                @if ($sku = Arr::get($orderProduct->options, 'sku'))
                                                    <p>{{ trans('plugins/ecommerce::shipping.sku') }} : <span>{{ $sku }}</span></p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="order-border text-end p-small p-sm-r">
                                    <strong class="item-quantity">{{ $orderProduct->qty }}</strong>
                                    <span class="item-multiplier mr5">×</span>
                                    <b class="color-blue-line-through">{{ format_price($orderProduct->price) }}</b>
                                </td>
                                <td class="order-border text-end p-small p-sm-r border-none-r">
                                    <span>{{ format_price($orderProduct->price * $orderProduct->qty) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flexbox-grid-default p-t15 p-b15 height-light bg-order">
                    <div class="flexbox-content">
                        <table>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center p-sm-r border-none">
                                        <a href="{{ $orderEditRouteName ? route($orderEditRouteName, $shipment->order_id) : '#' }}" target="_blank" class="d-inline-block mt-2">
                                            {{ trans('plugins/ecommerce::shipping.view_order', ['order_id' => $shipment->order->code]) }}
                                            <i class="fa fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
