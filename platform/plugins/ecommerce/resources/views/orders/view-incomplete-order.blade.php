@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1036">
        @if ($order->token)
            <div class="ui-layout__item mb20">
                <div class="ui-banner ui-banner--status-info">
                    <div class="ui-banner__ribbon">
                        <svg class="svg-next-icon svg-next-icon-size-20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                            <path fill="currentColor" d="M4.8 17.6h22.4L30.4 8 4.8 4.8z"></path>
                            <path d="M27.2 28.8c-.882 0-1.6-.718-1.6-1.6s.718-1.6 1.6-1.6c.882 0 1.6.718 1.6 1.6s-.718 1.6-1.6 1.6zM6.4 27.2c0 .882-.718 1.6-1.6 1.6s-1.6-.718-1.6-1.6c0-.882.718-1.6 1.6-1.6s1.6.718 1.6 1.6zM28.266 9.346L26.046 16H6.402V6.613L28.27 9.346zM27.2 22.4H6.4v-3.2h20.8c.69 0 1.3-.44 1.518-1.094l3.2-9.6c.15-.454.09-.954-.165-1.358-.256-.406-.68-.675-1.155-.734L6.4 3.388V1.6C6.4.72 5.683 0 4.8 0H1.6C.717 0 0 .72 0 1.6s.717 1.6 1.6 1.6h1.6v19.495C1.34 23.36 0 25.117 0 27.2 0 29.848 2.154 32 4.8 32s4.8-2.153 4.8-4.8c0-.564-.115-1.097-.294-1.6h13.39c-.18.503-.295 1.036-.295 1.6 0 2.647 2.16 4.8 4.8 4.8s4.8-2.153 4.8-4.8c0-2.645-2.15-4.8-4.8-4.8z"></path>
                        </svg>
                    </div>
                    <div class="ui-banner__content ws-nm">
                        <h2 class="ui-banner__title">
                            {{ trans('plugins/ecommerce::order.incomplete_order_description_1') }}
                        </h2>
                        <h2 class="ui-banner__title">
                            {{ trans('plugins/ecommerce::order.incomplete_order_description_2') }}
                        </h2>
                        <div class="ws-nm">
                            <input type="text" class="next-input" onclick="this.focus(); this.select();" value="{{ route('public.checkout.recover', $order->token) }}">
                            <br>
                            @if ($order->user->email || $order->address->email)
                                <button class="btn btn-secondary btn-trigger-send-order-recover-modal" data-action="{{ route('orders.send-order-recover-email', $order->id) }}">{{ trans('plugins/ecommerce::order.send_an_email_to_recover_this_order') }}</button>
                            @else
                                <strong><i>{{ trans('plugins/ecommerce::order.cannot_send_order_recover_to_mail') }}</i></strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="flexbox-grid">
            <div class="flexbox-content">
                <div class="wrapper-content mb20">
                    <div class="pd-all-20">
                        <label class="title-product-main text-no-bold">{{ trans('plugins/ecommerce::order.order_information') }}</label>
                    </div>
                    <div class="pd-all-10-20 border-top-title-main">
                        <div class="clearfix">
                            <div class="table-wrapper p-none mb20 ps-relative">
                                <table class="table-normal">
                                    <tbody>
                                        @php
                                            $order->load(['products.product']);
                                        @endphp
                                        @foreach ($order->products as $orderProduct)
                                            @php
                                                $product = $orderProduct->product;
                                            @endphp
                                            @if ($product && $product->original_product)
                                                <tr>
                                                    <td class="width-60-px min-width-60-px">
                                                        <div class="wrap-img"><img class="thumb-image thumb-image-cartorderlist" src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}"></div>
                                                    </td>
                                                    <td class="pl5 p-r5">
                                                        @if ($product->original_product->id)
                                                            <a target="_blank" href="{{ route('products.edit', $product->original_product->id) }}" title="{{ $orderProduct->product_name }}">{{ $orderProduct->product_name }}</a>
                                                        @else
                                                            <span>{{ $orderProduct->product_name }}</span>
                                                        @endif
                                                        <p>
                                                            <small>{{ $product->variation_attributes }}</small>
                                                        </p>
                                                        @if ($product->sku)
                                                            <p>{{ trans('plugins/ecommerce::order.sku') }} : <span>{{ $product->sku }}</span></p>
                                                        @endif
                                                    </td>
                                                    <td class="pl5 p-r5 width-100-px min-width-100-px text-end">
                                                        <span>{{ format_price($orderProduct->price) }}</span>
                                                    </td>
                                                    <td class="pl5 p-r5 width-20-px min-width-20-px text-center"> x</td>
                                                    <td class="pl5 p-r5 width-30-px min-width-30-px text-start">
                                                        <span class="item-quantity text-end">{{ $orderProduct->qty }}</span>
                                                    </td>
                                                    <td class="pl5 p-r5 width-100-px min-width-130-px text-end">{{ format_price($orderProduct->price * $orderProduct->qty) }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="flexbox-grid-default">
                            <div class="flexbox-auto-content"></div>
                            <div class="flexbox-auto-content">
                                <div class="table-wrapper">
                                    <table class="table-normal table-none-border">
                                        <tbody>
                                        <tr>
                                            <td class="text-end color-subtext">{{ trans('plugins/ecommerce::order.quantity') }}</td>
                                            <td class="text-end pl10">
                                                <span>{{ number_format($order->products->sum('qty')) }}</span>
                                            </td>
                                        </tr>
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
                                                    <p class="mb0">{!! BaseHelper::clean(trans('plugins/ecommerce::order.coupon_code', ['code' => Html::tag('strong', $order->coupon_code)->toHtml()])) !!}</p>
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
                                                <p class="mb0 font-size-12px">{{ $weight }} {{ ecommerce_weight_unit(true) }}</p>
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
                                                @if (is_plugin_active('payment') && $order->payment->id)
                                                    <p class="mb0  font-size-12px"><a href="{{ route('payment.show', $order->payment->id) }}" target="_blank">{{ $order->payment->payment_channel->label() }}</a>
                                                    </p>
                                                @endif
                                            </td>
                                            <td class="text-end text-no-bold p-none-t pl10">
                                                @if (is_plugin_active('payment') && $order->payment->id)
                                                    <a href="{{ route('payment.show', $order->payment->id) }}" target="_blank">
                                                        <span>{{ format_price($order->amount) }}</span>
                                                    </a>
                                                @else
                                                    <span>{{ format_price($order->amount) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        {!! apply_filters('ecommerce_admin_order_extra_info', null, $order) !!}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wrapper-content mb20">
                    <div class="pd-all-20 p-none-b">
                        <label class="title-product-main text-no-bold">{{ trans('plugins/ecommerce::order.additional_information') }}</label>
                    </div>
                    <div class="pd-all-10-20">
                        <form action="{{ route('orders.edit', $order->id) }}">
                            <label class="text-title-field">{{ trans('plugins/ecommerce::order.order_note') }}</label>
                            <textarea class="ui-text-area textarea-auto-height" name="description" placeholder="{{ trans('plugins/ecommerce::order.order_note_placeholder') }}" rows="2">{{ $order->description }}</textarea>
                            <div class="mt15 mb15 text-end">
                                <button type="button" class="btn btn-primary btn-update-order">{{ trans('plugins/ecommerce::order.save_note') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="flexbox-content flexbox-right">
                <div class="wrapper-content mb20">
                    <div class="next-card-section p-none-b">
                        <div class="flexbox-grid-default">
                            <div class="flexbox-auto-content">
                                <label class="title-product-main"><strong>{{ trans('plugins/ecommerce::order.customer_label') }}</strong></label>
                            </div>
                            <div class="flexbox-auto-left">
                                <img class="width-30-px radius-cycle" width="40" src="{{ $order->user->id ? $order->user->avatar_url : $order->address->avatar_url }}" alt="{{ $order->address->name }}">
                            </div>
                        </div>
                    </div>
                    <div class="next-card-section border-none-t">
                        <ul class="ws-nm">
                            <li class="overflow-ellipsis">
                                <div class="mb5">
                                    <a class="hover-underline text-capitalize" href="#">{{ $order->user->name ?: $order->address->name }}</a>
                                </div>
                                @if ($order->user->id)
                                    <div><i class="fas fa-inbox mr5"></i><span>{{ $order->user->orders()->count() }}</span> {{ trans('plugins/ecommerce::order.orders') }}</div>
                                @endif
                                <ul class="ws-nm text-infor-subdued">
                                    <li class="overflow-ellipsis"><a class="hover-underline" href="mailto:{{ $order->user->email ?: $order->address->email }}">{{ $order->user->email ?: $order->address->email }}</a></li>
                                    @if ($order->user->id)
                                        <li><div>{{ trans('plugins/ecommerce::order.have_an_account_already') }}</div></li>
                                    @else
                                        <li><div>{{ trans('plugins/ecommerce::order.dont_have_an_account_yet') }}</div></li>
                                    @endif
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="next-card-section">
                        <ul class="ws-nm">
                            <li class="clearfix">
                                <div class="flexbox-grid-default">
                                    <div class="flexbox-auto-content">
                                        <label class="title-text-second"><strong>{{ trans('plugins/ecommerce::order.shipping_address') }}</strong></label>
                                    </div>
                                </div>
                            </li>
                            <li class="text-infor-subdued mt15">
                                <div>{{ $order->address->name }}</div>
                                <div>
                                    <a href="tel:{{ $order->address->phone }}">
                                        <span><i class="fa fa-phone-square cursor-pointer mr5"></i></span>
                                        <span>{{ $order->address->phone }}</span>
                                    </a>
                                </div>
                                <div>
                                    <div>{{ $order->address->address }}</div>
                                    <div>{{ $order->address->city_name }}</div>
                                    <div>{{ $order->address->state_name }}</div>
                                    <div>{{ $order->address->country_name }}</div>
                                    <div>
                                        <a target="_blank" class="hover-underline" href="https://maps.google.com/?q={{ $order->full_address }}">{{ trans('plugins/ecommerce::order.see_maps') }}</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    @if ($order->referral()->count())
                        <div class="next-card-section">
                            <div class="flexbox-grid-default flexbox-align-items-center mb-2">
                                <div class="flexbox-auto-content-left">
                                    <label class="title-text-second"><strong>{{ trans('plugins/ecommerce::order.referral') }}</strong></label>
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
                                            <li>{{ trans('plugins/ecommerce::order.referral_data.' . $field) }}: <strong style="word-break: break-all">{{ $order->referral->{$field} }}</strong></li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="wrapper-content mb20">
                    <div class="pd-all-20">
                        <button data-action="{{ route('orders.mark-as-completed', $order->id) }}" class="btn btn-warning btn-mark-order-as-completed-modal">{{ trans('plugins/ecommerce::order.mark_as_completed.name') }}</button>&nbsp;
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-core-base::modal
        id="send-order-recover-email-modal"
        :title="trans('plugins/ecommerce::order.notice_about_incomplete_order')"
        type="info"
        button-id="confirm-send-recover-email-button"
        :button-label="trans('plugins/ecommerce::order.send')"
    >
        {!! trans('plugins/ecommerce::order.notice_about_incomplete_order_description', ['email' => $order->user->id ? $order->user->email : $order->address->email]) !!}
    </x-core-base::modal>

    <x-core-base::modal
        id="mark-order-as-completed-modal"
        :title="trans('plugins/ecommerce::order.mark_as_completed.modal_title')"
        type="info"
        button-id="confirm-mark-as-completed-button"
        :button-label="trans('plugins/ecommerce::order.mark_as_completed.name')"
    >
        {{ trans('plugins/ecommerce::order.mark_as_completed.modal_description') }}
    </x-core-base::modal>
@endsection
