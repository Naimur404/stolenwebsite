@extends(MarketplaceHelper::viewPath('dashboard.layouts.master'))

@section('content')
    <div class="flexbox-layout-sections" id="main-order-content" style="margin: 0 -20px;">
        @if ($order->status == \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED)
            <div class="ui-layout__section">
                <div class="ui-layout__item">
                    <div class="ui-banner ui-banner--status-warning">
                        <div class="ui-banner__ribbon">
                            <svg class="svg-next-icon svg-next-icon-size-20">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Circle-Alert</title><path fill="currentColor" d="M19 10c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"></path><path d="M10 0C4.486 0 0 4.486 0 10s4.486 10 10 10 10-4.486 10-10S15.514 0 10 0zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-13c-.552 0-1 .447-1 1v4c0 .553.448 1 1 1s1-.447 1-1V6c0-.553-.448-1-1-1zm0 8c-.552 0-1 .447-1 1s.448 1 1 1 1-.447 1-1-.448-1-1-1z"></path></svg>
                            </svg>
                        </div>
                        <div class="ui-banner__content">
                            <h2 class="ui-banner__title">{{ trans('plugins/ecommerce::order.order_canceled') }}</h2>
                            <div class="ws-nm">
                                {{ trans('plugins/ecommerce::order.order_was_canceled_at') }}
                                <strong>{{ BaseHelper::formatDate($order->updated_at, 'H:i d/m/Y') }}</strong>.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="flexbox-layout-section-primary mt20">
            <div class="ui-layout__item">
                <div class="wrapper-content">
                    <div class="pd-all-20">
                        <div class="flexbox-grid-default">
                            <div class="flexbox-auto-right mr5">
                                <label
                                    class="title-product-main text-no-bold">{{ trans('plugins/ecommerce::order.order_information') }} {{ $order->code }}</label>
                            </div>
                        </div>
                        <div class="mt20">
                            @if ($order->shipment->id)
                                <svg class="svg-next-icon svg-next-icon-size-16 next-icon--right-spacing-quartered">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" enable-background="new 0 0 16 16">
                                        <g>
                                            <path
                                                d="M13.992 0H2.1C.94 0 0 .94 0 2.1v12.244C0 15.305.785 16 1.75 16H14.34c.964 0 1.658-.694 1.658-1.658V2.1C16 .94 15.15 0 13.992 0zM14 2v8h-1.757C11.28 10 10 11.28 10 12.243v.7c0 .193.337.057.144.057H5.247c-.193 0-.247.136-.247-.057v-.7C5 11.28 4.113 10 3.148 10H2V2h12zM7.117 9.963c.167.16.437.16.603.002l5.17-5.042c.165-.16.165-.422 0-.583l-.604-.583c-.166-.16-.437-.16-.603 0L7.42 7.924 5.694 6.24c-.166-.16-.437-.16-.603 0l-.604.582c-.166.162-.166.423 0 .584l2.63 2.557z"></path>
                                        </g>
                                    </svg>
                                </svg>
                                <strong class="ml5">{{ trans('plugins/ecommerce::order.completed') }}</strong>
                            @else
                                <svg class="svg-next-icon svg-next-icon-size-16 svg-next-icon-gray">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" enable-background="new 0 0 16 16">
                                        <g>
                                            <path
                                                d="M13.9130435,0 L2.08695652,0 C0.936347826,0 0,0.936347826 0,2.08695652 L0,14.2608696 C0,15.2194783 0.780521739,16 1.73913043,16 L14.2608696,16 C15.2194783,16 16,15.2194783 16,14.2608696 L16,2.08695652 C16,0.936347826 15.0636522,0 13.9130435,0 L13.9130435,0 Z M13.9130435,2.08695652 L13.9130435,10.4347826 L12.173913,10.4347826 C11.2153043,10.4347826 10.4347826,11.2153043 10.4347826,12.173913 L10.4347826,12.8695652 C10.4347826,13.0615652 10.2789565,13.2173913 10.0869565,13.2173913 L5.2173913,13.2173913 C5.0253913,13.2173913 4.86956522,13.0615652 4.86956522,12.8695652 L4.86956522,12.173913 C4.86956522,11.2153043 4.08904348,10.4347826 3.13043478,10.4347826 L2.08695652,10.4347826 L2.08695652,2.08695652 L13.9130435,2.08695652 L13.9130435,2.08695652 Z"></path>
                                        </g>
                                    </svg>
                                </svg>
                                <strong class="ml5">{{ trans('plugins/ecommerce::order.completed') }}</strong>
                            @endif
                        </div>
                    </div>
                    <div class="pd-all-20 p-none-t border-top-title-main">
                        <div class="table-wrap">
                            <table class="table-order table-divided">
                                <tbody>
                                    @foreach ($order->products as $orderProduct)
                                        @php
                                            $product = $orderProduct->product;
                                        @endphp
                                        <tr>
                                            <td class="width-60-px min-width-60-px vertical-align-t">
                                                <div class="wrap-img">
                                                    <img class="thumb-image thumb-image-cartorderlist"
                                                        src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                        alt="{{ $orderProduct->product_name }}">
                                                </div>
                                            </td>
                                            <td class="pl5 p-r5 min-width-200-px">
                                                <a class="text-underline hover-underline pre-line" target="_blank"
                                                    href="{{ $product->id && $product->original_product->id ? route('marketplace.vendor.products.edit', $product->original_product->id) : '#' }}"
                                                    title="{{ $orderProduct->product_name }}">
                                                    {{ $orderProduct->product_name }}
                                                </a>
                                                &nbsp;
                                                @if ($sku = Arr::get($orderProduct->options, 'sku'))
                                                    ({{ trans('plugins/ecommerce::order.sku') }}: <strong>{{ $sku }}</strong>)
                                                @endif
                                                @if ($attributes = Arr::get($orderProduct->options, 'attributes'))
                                                    <p class="mb-0">
                                                        <small>{{ $attributes }}</small>
                                                    </p>
                                                @endif

                                                @include('plugins/ecommerce::themes.includes.cart-item-options-extras', ['options' => $orderProduct->options])

                                                {!! apply_filters(ECOMMERCE_ORDER_DETAIL_EXTRA_HTML, null) !!}
                                                @if ($order->shipment->id)
                                                    <ul class="unstyled">
                                                        <li class="simple-note">
                                                            <a>
                                                                <span>{{ $orderProduct->qty }}</span>
                                                                <span class="text-lowercase"> {{ trans('plugins/ecommerce::order.completed') }}</span>
                                                            </a>
                                                            <ul class="dom-switch-target line-item-properties small">
                                                                <li class="ws-nm">
                                                                    <span class="bull">↳</span>
                                                                    <span
                                                                        class="black">{{ trans('plugins/ecommerce::order.shipping') }} </span>
                                                                    <strong>{{ $order->shipping_method_name }}</strong>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                @endif
                                            </td>
                                            <td class="pl5 p-r5 text-end">
                                                <div class="inline_block">
                                                    <span>{{ format_price($orderProduct->price) }}</span>
                                                </div>
                                            </td>
                                            <td class="pl5 p-r5 text-center">x</td>
                                            <td class="pl5 p-r5">
                                                <span>{{ $orderProduct->qty }}</span>
                                            </td>
                                            <td class="pl5 text-end">{{ format_price($orderProduct->price * $orderProduct->qty) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="pd-all-20 p-none-t">
                        <div class="flexbox-grid-default">
                            <div class="flexbox-auto-right p-r5">

                            </div>
                            <div class="flexbox-auto-right pl5">
                                <div class="table-wrap">
                                    <table class="table-normal table-none-border table-color-gray-text">
                                        <tbody>
                                        <tr>
                                            <td class="text-end color-subtext">{{ trans('plugins/ecommerce::order.sub_amount') }}</td>
                                            <td class="text-end pl10">
                                                <span>{{ format_price($order->sub_total) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-end color-subtext mt10">
                                                <p class="mb0">{{ trans('plugins/ecommerce::order.discount') }}</p>
                                                @if ($order->coupon_code)
                                                    <p class="mb0">{!! trans('plugins/ecommerce::order.coupon_code', ['code' => Html::tag('strong', $order->coupon_code)->toHtml()])  !!}</p>
                                                @elseif ($order->discount_description)
                                                    <p class="mb0">{{ $order->discount_description }}</p>
                                                @endif
                                            </td>
                                            <td class="text-end p-none-b pl10">
                                                <p class="mb0">{{ format_price($order->discount_amount) }}</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-end color-subtext mt10">
                                                <p class="mb0">{{ trans('plugins/ecommerce::order.shipping_fee') }}</p>
                                                <p class="mb0 font-size-12px">{{ $order->shipping_method_name }}</p>
                                                <p class="mb0 font-size-12px">{{ ecommerce_convert_weight($weight) }} {{ ecommerce_weight_unit(true) }}</p>
                                            </td>
                                            <td class="text-end p-none-t pl10">
                                                <p class="mb0">{{ format_price($order->shipping_amount) }}</p>
                                            </td>
                                        </tr>
                                        @if (EcommerceHelper::isTaxEnabled())
                                            <tr>
                                                <td class="text-end color-subtext mt10">
                                                    <p class="mb0">{{ trans('plugins/ecommerce::order.tax') }}</p>
                                                </td>
                                                <td class="text-end p-none-t pl10">
                                                    <p class="mb0">{{ format_price($order->tax_amount) }}</p>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="text-end mt10">
                                                <p class="mb0 color-subtext">{{ trans('plugins/ecommerce::order.total_amount') }}</p>
                                                @if ($order->payment->id)
                                                    <p class="mb0  font-size-12px">{{ $order->payment->payment_channel->label() }}</p>
                                                @endif
                                            </td>
                                            <td class="text-end text-no-bold p-none-t pl10">
                                                <span>{{ format_price($order->amount) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom"></td>
                                            <td class="border-bottom"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-end color-subtext">{{ trans('plugins/ecommerce::order.paid_amount') }}</td>
                                            <td class="text-end color-subtext pl10">
                                                <span>{{ format_price($order->payment->status == \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED ? $order->payment->amount : 0) }}</span>
                                            </td>
                                        </tr>
                                        @if ($order->payment->status == \Botble\Payment\Enums\PaymentStatusEnum::REFUNDED)
                                            <tr class="hidden">
                                                <td class="text-end color-subtext">{{ trans('plugins/ecommerce::order.refunded_amount') }}</td>
                                                <td class="text-end pl10">
                                                    <span>{{ format_price($order->payment->amount) }}</span>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="hidden">
                                            <td class="text-end color-subtext">{{ trans('plugins/ecommerce::order.amount_received') }}</td>
                                            <td class="text-end pl10">
                                                <span>{{ format_price($order->payment->status == \Botble\Payment\Enums\PaymentStatusEnum::COMPLETED ? $order->amount : 0) }}</span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <br>
                                @if ($order->isInvoiceAvailable())
                                    <div class="text-end">
                                        <a href="{{ route('marketplace.vendor.orders.generate-invoice', $order->id) }}"
                                           class="btn btn-info">
                                            <i class="fa fa-download"></i> {{ trans('plugins/ecommerce::order.download_invoice') }}
                                        </a>
                                    </div>
                                @endif
                                <div class="pd-all-20">
                                    <form action="{{ route('marketplace.vendor.orders.edit', $order->id) }}">
                                        <label
                                            class="text-title-field">{{ trans('plugins/ecommerce::order.note') }}</label>
                                        <textarea class="ui-text-area textarea-auto-height" name="description" rows="3"
                                                  placeholder="{{ trans('plugins/ecommerce::order.add_note') }}">{{ $order->description }}</textarea>
                                        <div class="mt10">
                                            <button type="button"
                                                    class="btn btn-primary btn-update-order">{{ trans('plugins/ecommerce::order.save') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pd-all-20 border-top-title-main">
                        <div class="flexbox-grid-default flexbox-align-items-center">
                            <div class="flexbox-auto-left">
                                <svg
                                    class="svg-next-icon svg-next-icon-size-20 @if ($order->is_confirmed) svg-next-icon-green @else svg-next-icon-gray @endif">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path
                                            d="M7 18c-.265 0-.52-.105-.707-.293l-6-6c-.39-.39-.39-1.023 0-1.414s1.023-.39 1.414 0l5.236 5.236L18.24 2.35c.36-.42.992-.468 1.41-.11.42.36.47.99.11 1.41l-12 14c-.182.212-.444.338-.722.35H7z"></path>
                                    </svg>
                                </svg>
                            </div>
                            <div class="flexbox-auto-right ml15 mr15 text-upper">
                                @if ($order->is_confirmed)
                                    <span>{{ trans('plugins/ecommerce::order.order_was_confirmed') }}</span>
                                @else
                                    <span>{{ trans('plugins/ecommerce::order.confirm_order') }}</span>
                                @endif
                            </div>
                            @if (!$order->is_confirmed)
                                <div class="flexbox-auto-left">
                                    <form action="{{ route('marketplace.vendor.orders.confirm') }}">
                                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                                        <button
                                            class="btn btn-primary btn-confirm-order">{{ trans('plugins/ecommerce::order.confirm') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if ($order->status == \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED)
                        <div class="pd-all-20 border-top-title-main">
                            <div class="flexbox-grid-default flexbox-flex-wrap flexbox-align-items-center">
                                <div class="flexbox-auto-left">
                                    <svg class="svg-next-icon svg-next-icon-size-24 svg-next-icon-gray">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" enable-background="new 0 0 24 24">
                                            <path
                                                d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.6 0 12 0zm0 4c1.4 0 2.7.4 3.9 1L12 8.8 8.8 12 5 15.9c-.6-1.1-1-2.5-1-3.9 0-4.4 3.6-8 8-8zm0 16c-1.4 0-2.7-.4-3.9-1l3.9-3.9 3.2-3.2L19 8.1c.6 1.1 1 2.5 1 3.9 0 4.4-3.6 8-8 8z"></path>
                                        </svg>
                                    </svg>
                                </div>
                                <div class="flexbox-auto-content ml15 mr15 text-upper">
                                    <span>{{ trans('plugins/ecommerce::order.order_was_canceled') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="pd-all-20 border-top-title-main">
                        <div class="flexbox-grid-default flexbox-flex-wrap flexbox-align-items-center">
                            @if ($order->status == \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED && !$order->shipment->id)
                                <div class="flexbox-auto-left">
                                    <svg class="svg-next-icon svg-next-icon-size-20 svg-next-icon-green">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path
                                                d="M7 18c-.265 0-.52-.105-.707-.293l-6-6c-.39-.39-.39-1.023 0-1.414s1.023-.39 1.414 0l5.236 5.236L18.24 2.35c.36-.42.992-.468 1.41-.11.42.36.47.99.11 1.41l-12 14c-.182.212-.444.338-.722.35H7z"></path>
                                        </svg>
                                    </svg>
                                </div>
                                <div class="flexbox-auto-content ml15 mr15 text-upper">
                                    <span>{{ trans('plugins/ecommerce::order.all_products_are_not_delivered') }}</span>
                                </div>
                            @else
                                @if ($order->shipment->id)
                                    <div class="flexbox-auto-left">
                                        <svg class="svg-next-icon svg-next-icon-size-20 svg-next-icon-green">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path
                                                    d="M7 18c-.265 0-.52-.105-.707-.293l-6-6c-.39-.39-.39-1.023 0-1.414s1.023-.39 1.414 0l5.236 5.236L18.24 2.35c.36-.42.992-.468 1.41-.11.42.36.47.99.11 1.41l-12 14c-.182.212-.444.338-.722.35H7z"></path>
                                            </svg>
                                        </svg>
                                    </div>
                                    <div class="flexbox-auto-content ml15 mr15 text-upper">
                                        <span>{{ trans('plugins/ecommerce::order.delivery') }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    @if (!$order->shipment->id)
                        <div class="shipment-create-wrap hidden"></div>
                    @else
                        @include(MarketplaceHelper::viewPath('dashboard.orders.shipment-detail'), ['shipment' => $order->shipment])
                    @endif
                </div>
                <div class="mt20 mb20">
                    <div>
                        <div class="comment-log ws-nm">
                            <div class="comment-log-title">
                                <label
                                    class="bold-light m-xs-b hide-print">{{ trans('plugins/ecommerce::order.history') }}</label>
                            </div>
                            <div class="comment-log-timeline">
                                <div class="column-left-history ps-relative" id="order-history-wrapper">
                                    @foreach ($order->histories()->orderBy('id', 'DESC')->get() as $history)
                                        <div class="item-card">
                                            <div class="item-card-body clearfix">
                                                <div
                                                    class="item comment-log-item comment-log-item-date ui-feed__timeline">
                                                    <div class="ui-feed__item ui-feed__item--message">
                                                        <span
                                                            class="ui-feed__marker @if ($history->user_id) ui-feed__marker--user-action @endif"></span>
                                                        <div class="ui-feed__message">
                                                            <div class="timeline__message-container">
                                                                <div class="timeline__inner-message">
                                                                    @if (in_array($history->action, ['confirm_payment', 'refund']))
                                                                        <a href="#"
                                                                           class="text-no-bold show-timeline-dropdown hover-underline"
                                                                           data-target="#history-line-{{ $history->id }}">
                                                                            <span>{{ OrderHelper::processHistoryVariables($history) }}</span>
                                                                        </a>
                                                                    @else
                                                                        <span>{{ OrderHelper::processHistoryVariables($history) }}</span>
                                                                    @endif
                                                                </div>
                                                                <time class="timeline__time">
                                                                    <span>{{ $history->created_at }}</span></time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if ($history->action == 'refund' && Arr::get($history->extras, 'amount', 0) > 0)
                                                        <div class="timeline-dropdown"
                                                             id="history-line-{{ $history->id }}">
                                                            <table>
                                                                <tbody>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.order_number') }}</th>
                                                                    <td>
                                                                        <a href="{{ route('marketplace.vendor.orders.edit', $order->id) }}"
                                                                           title="{{ $order->code }}">{{ $order->code }}</a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.description') }}</th>
                                                                    <td>{{ $history->description . ' ' . trans('plugins/ecommerce::order.from') . ' ' . $order->payment->payment_channel->label() }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.amount') }}</th>
                                                                    <td>{{ format_price(Arr::get($history->extras, 'amount', 0)) }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.status') }}</th>
                                                                    <td>{{ trans('plugins/ecommerce::order.successfully') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.transaction_type') }}</th>
                                                                    <td>{{ trans('plugins/ecommerce::order.refund') }}</td>
                                                                </tr>
                                                                @if (trim($history->user->name))
                                                                    <tr>
                                                                        <th>{{ trans('plugins/ecommerce::order.staff') }}</th>
                                                                        <td>{{ $history->user->name ?: trans('plugins/ecommerce::order.n_a') }}</td>
                                                                    </tr>
                                                                @endif
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.refund_date') }}</th>
                                                                    <td>{{ $history->created_at }}</td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                    @if ($history->action == 'confirm_payment' && $order->payment)
                                                        <div class="timeline-dropdown"
                                                             id="history-line-{{ $history->id }}">
                                                            <table>
                                                                <tbody>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.order_number') }}</th>
                                                                    <td>
                                                                        <a href="{{ route('marketplace.vendor.orders.edit', $order->id) }}"
                                                                           title="{{ $order->code }}">{{ $order->code }}</a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.description') }}</th>
                                                                    <td>{!! trans('plugins/ecommerce::order.mark_payment_as_confirmed', ['method' => $order->payment->payment_channel->label()]) !!}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.transaction_amount') }}</th>
                                                                    <td>{{ format_price($order->payment->amount) }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.payment_gateway') }}</th>
                                                                    <td>{{ $order->payment->payment_channel->label() }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.status') }}</th>
                                                                    <td>{{ trans('plugins/ecommerce::order.successfully') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.transaction_type') }}</th>
                                                                    <td>{{ trans('plugins/ecommerce::order.confirm') }}</td>
                                                                </tr>
                                                                @if (trim($history->user->name))
                                                                    <tr>
                                                                        <th>{{ trans('plugins/ecommerce::order.staff') }}</th>
                                                                        <td>{{ $history->user->name ?: trans('plugins/ecommerce::order.n_a') }}</td>
                                                                    </tr>
                                                                @endif
                                                                <tr>
                                                                    <th>{{ trans('plugins/ecommerce::order.payment_date') }}</th>
                                                                    <td>{{ $history->created_at }}</td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                    @if ($history->action == 'send_order_confirmation_email')
                                                        <div class="ui-feed__item ui-feed__item--action">
                                                            <span class="ui-feed__spacer"></span>
                                                            <div class="timeline__action-group">
                                                                <a href="#"
                                                                   class="btn hide-print timeline__action-button hover-underline btn-trigger-resend-order-confirmation-modal"
                                                                   data-action="{{ route('marketplace.vendor.orders.send-order-confirmation-email', $history->order_id) }}">{{ trans('plugins/ecommerce::order.resend') }}</a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="flexbox-layout-section-secondary mt20">
            <div class="ui-layout__item">
                <div class="wrapper-content mb20">
                    <div class="next-card-section p-none-b">
                        <div class="flexbox-grid-default flexbox-align-items-center">
                            <div class="flexbox-auto-content-left">
                                <label
                                    class="title-product-main text-no-bold">{{ trans('plugins/ecommerce::order.customer_label') }}</label>
                            </div>
                            <div class="flexbox-auto-left">
                                <img class="width-30-px radius-cycle" width="40"
                                     src="{{ $order->user->id ? $order->user->avatar_url : $order->address->avatar_url }}"
                                     alt="{{ $order->address->name }}">
                            </div>
                        </div>
                    </div>
                    <div class="next-card-section border-none-t">
                        <div class="mb5">
                            <strong class="text-capitalize">{{ $order->user->name ?: $order->address->name }}</strong>
                        </div>
                        @if ($order->user->id)
                            <div>
                                <i class="fas fa-inbox mr5"></i><span>{{ $order->user->orders()->count() }}</span> {{ trans('plugins/ecommerce::order.orders') }}
                            </div>
                        @endif
                        <ul class="ws-nm text-infor-subdued">
                            <li class="overflow-ellipsis">
                                <a class="hover-underline"
                                   href="mailto:{{ $order->user->email ?: $order->address->email }}">{{ $order->user->email ?: $order->address->email }}</a>
                            </li>
                            @if ($order->user->id)
                                <li>
                                    <div>{{ trans('plugins/ecommerce::order.have_an_account_already') }}</div>
                                </li>
                            @else
                                <li>
                                    <div>{{ trans('plugins/ecommerce::order.dont_have_an_account_yet') }}</div>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="next-card-section">
                        @if (!EcommerceHelper::countDigitalProducts($order->products))
                            <div class="flexbox-grid-default flexbox-align-items-center">
                                <div class="flexbox-auto-content-left">
                                    <label
                                        class="title-text-second"><strong>{{ trans('plugins/ecommerce::order.shipping_address') }}</strong></label>
                                </div>
                                @if ($order->status != \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED)
                                    <div class="flexbox-auto-content-right text-end">
                                        <a href="#" class="btn-trigger-update-shipping-address">
                                        <span data-placement="top" data-bs-toggle="tooltip"
                                              data-bs-original-title="{{ trans('plugins/ecommerce::order.update_address') }}">
                                            <svg class="svg-next-icon svg-next-icon-size-12">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 55.25 55.25">
                                                    <path
                                                        d="M52.618,2.631c-3.51-3.508-9.219-3.508-12.729,0L3.827,38.693C3.81,38.71,3.8,38.731,3.785,38.749  c-0.021,0.024-0.039,0.05-0.058,0.076c-0.053,0.074-0.094,0.153-0.125,0.239c-0.009,0.026-0.022,0.049-0.029,0.075  c-0.003,0.01-0.009,0.02-0.012,0.03l-3.535,14.85c-0.016,0.067-0.02,0.135-0.022,0.202C0.004,54.234,0,54.246,0,54.259  c0.001,0.114,0.026,0.225,0.065,0.332c0.009,0.025,0.019,0.047,0.03,0.071c0.049,0.107,0.11,0.21,0.196,0.296  c0.095,0.095,0.207,0.168,0.328,0.218c0.121,0.05,0.25,0.075,0.379,0.075c0.077,0,0.155-0.009,0.231-0.027l14.85-3.535  c0.027-0.006,0.051-0.021,0.077-0.03c0.034-0.011,0.066-0.024,0.099-0.039c0.072-0.033,0.139-0.074,0.201-0.123  c0.024-0.019,0.049-0.033,0.072-0.054c0.008-0.008,0.018-0.012,0.026-0.02l36.063-36.063C56.127,11.85,56.127,6.14,52.618,2.631z   M51.204,4.045c2.488,2.489,2.7,6.397,0.65,9.137l-9.787-9.787C44.808,1.345,48.716,1.557,51.204,4.045z M46.254,18.895l-9.9-9.9  l1.414-1.414l9.9,9.9L46.254,18.895z M4.961,50.288c-0.391-0.391-1.023-0.391-1.414,0L2.79,51.045l2.554-10.728l4.422-0.491  l-0.569,5.122c-0.004,0.038,0.01,0.073,0.01,0.11c0,0.038-0.014,0.072-0.01,0.11c0.004,0.033,0.021,0.06,0.028,0.092  c0.012,0.058,0.029,0.111,0.05,0.165c0.026,0.065,0.057,0.124,0.095,0.181c0.031,0.046,0.062,0.087,0.1,0.127  c0.048,0.051,0.1,0.094,0.157,0.134c0.045,0.031,0.088,0.06,0.138,0.084C9.831,45.982,9.9,46,9.972,46.017  c0.038,0.009,0.069,0.03,0.108,0.035c0.036,0.004,0.072,0.006,0.109,0.006c0,0,0.001,0,0.001,0c0,0,0.001,0,0.001,0h0.001  c0,0,0.001,0,0.001,0c0.036,0,0.073-0.002,0.109-0.006l5.122-0.569l-0.491,4.422L4.204,52.459l0.757-0.757  C5.351,51.312,5.351,50.679,4.961,50.288z M17.511,44.809L39.889,22.43c0.391-0.391,0.391-1.023,0-1.414s-1.023-0.391-1.414,0  L16.097,43.395l-4.773,0.53l0.53-4.773l22.38-22.378c0.391-0.391,0.391-1.023,0-1.414s-1.023-0.391-1.414,0L10.44,37.738  l-3.183,0.354L34.94,10.409l9.9,9.9L17.157,47.992L17.511,44.809z M49.082,16.067l-9.9-9.9l1.415-1.415l9.9,9.9L49.082,16.067z"/>
                                                </svg>
                                            </svg>
                                        </span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <ul class="ws-nm text-infor-subdued shipping-address-info">
                                    @include('plugins/ecommerce::orders.shipping-address.detail', ['address' => $order->address])
                                </ul>
                            </div>
                        @endif

                        @if (EcommerceHelper::isBillingAddressEnabled() && $order->billingAddress->id && $order->billingAddress->id != $order->shippingAddress->id)
                            <div class="flexbox-grid-default flexbox-align-items-center">
                                <div class="flexbox-auto-content-left">
                                    <label
                                        class="title-text-second"><strong>{{ trans('plugins/ecommerce::order.billing_address') }}</strong></label>
                                </div>
                            </div>
                            <div>
                                <ul class="ws-nm text-infor-subdued shipping-address-info">
                                    @include('plugins/ecommerce::orders.shipping-address.detail', ['address' => $order->billingAddress])
                                </ul>
                            </div>
                        @endif
                    </div>
                    @if ($order->referral()->count())
                        <div class="next-card-section">
                            <div class="flexbox-grid-default flexbox-align-items-center mb-2">
                                <div class="flexbox-auto-content-left">
                                    <label
                                        class="title-text-second"><strong>{{ trans('plugins/ecommerce::order.referral') }}</strong></label>
                                </div>
                            </div>
                            <div>
                                <ul class="ws-nm text-infor-subdued">
                                    @foreach (['ip',
                                        'landing_domain',
                                        'landing_page',
                                        'landing_params',
                                        'referral',
                                        'gclid',
                                        'fclid',
                                        'utm_source',
                                        'utm_campaign',
                                        'utm_medium',
                                        'utm_term',
                                        'utm_content',
                                        'referrer_url',
                                        'referrer_domain'] as $field)
                                        @if ($order->referral->{$field})
                                            <li>{{ trans('plugins/ecommerce::order.referral_data.' . $field) }}: <strong
                                                    style="word-break: break-all">{{ $order->referral->{$field} }}</strong>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($order->canBeCanceledByAdmin())
                    <div class="wrapper-content bg-gray-white mb20">
                        <div class="pd-all-20">
                            <a href="#" class="btn btn-warning btn-trigger-cancel-order"
                               data-target="{{ route('marketplace.vendor.orders.cancel', $order->id) }}">{{ trans('plugins/ecommerce::order.cancel') }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if ($order->status != \Botble\Ecommerce\Enums\OrderStatusEnum::CANCELED)
            <x-core-base::modal
                id="resend-order-confirmation-email-modal"
                :title="trans('plugins/ecommerce::order.resend_order_confirmation')"
                button-id="confirm-resend-confirmation-email-button"
                :button-label="trans('plugins/ecommerce::order.send')"
            >
                {!! trans('plugins/ecommerce::order.resend_order_confirmation_description', ['email' => $order->user->id ? $order->user->email : $order->address->email]) !!}
            </x-core-base::modal>

            <x-core-base::modal
                id="update-shipping-address-modal"
                :title="trans('plugins/ecommerce::order.update_address')"
                button-id="confirm-update-shipping-address-button"
                :button-label="trans('plugins/ecommerce::order.update')"
                size="md"
            >
                @include('plugins/ecommerce::orders.shipping-address.form', ['address' => $order->address, 'orderId' => $order->id, 'url' => route('marketplace.vendor.orders.update-shipping-address', $order->address->id ?? 0)])
            </x-core-base::modal>

            <x-core-base::modal
                id="cancel-order-modal"
                :title="trans('plugins/ecommerce::order.cancel_order_confirmation')"
                button-id="confirm-cancel-order-button"
                :button-label="trans('plugins/ecommerce::order.cancel_order')"
                type="warning"
            >
                {!! trans('plugins/ecommerce::order.cancel_order_confirmation_description') !!}
            </x-core-base::modal>

            @if ($order->shipment && $order->shipment->id)
                <x-core-base::modal
                    id="update-shipping-status-modal"
                    :title="trans('plugins/ecommerce::shipping.update_shipping_status')"
                    button-id="confirm-update-shipping-status-button"
                    :button-label="trans('plugins/ecommerce::order.update')"
                >
                    @include('plugins/marketplace::themes.dashboard.orders.shipping-status-modal', ['shipment' => $order->shipment, 'url' => route('marketplace.vendor.orders.update-shipping-status', $order->shipment->id)])
                </x-core-base::modal>
            @endif
        @endif
    </div>
@stop
