<div class="widget-body p-0" id="payment-histories">
    <div class="comment-log-timeline">
        <div class="column-left-history ps-relative" id="order-history-wrapper">
            <div class="item-card">
                <div class="item-card-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">{{ trans('plugins/ecommerce::payment.order') }}</th>
                            <th scope="col">{{ trans('plugins/ecommerce::payment.charge_id') }}</th>
                            <th scope="col">{{ trans('plugins/ecommerce::payment.amount') }}</th>
                            <th scope="col">{{ trans('plugins/ecommerce::payment.payment_method') }}</th>
                            <th scope="col">{{ trans('plugins/ecommerce::payment.status') }}</th>
                            <th scope="col">{{ trans('plugins/ecommerce::payment.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="text-start">
                                        @if ($payment->order->id)
                                            <a href="{{ route('orders.edit', $payment->order->id) }}" target="_blank">{{ $payment->order->code }} <i class="fa fa-external-link"></i></a>
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                    <td>{{ $payment->charge_id }}</td>
                                    <td>{{ $payment->amount }} {{ $payment->currency }}</td>
                                    <td>{{ $payment->payment_channel->label() }}</td>
                                    <td>{!! BaseHelper::clean($payment->status->toHtml()) !!}</td>
                                    <td class="text-center" style="width: 120px;">
                                        <a href="{{ route('payment.show', $payment->id) }}" class="btn btn-icon btn-sm btn-info me-1 btn-trigger-edit-payment" data-bs-toggle="tooltip" role="button" data-bs-original-title="{{ trans('core/base::forms.view_new_tab') }}" target="_blank">
                                            <i class="fa fa-external-link"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <td colspan="7" class="text-center">{{ trans('plugins/ecommerce::payment.no_data') }}</td>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<x-core-base::modal
    id="edit-payment-modal"
    :title="trans('plugins/ecommerce::payment.edit_payment')"
    button-id="confirm-edit-payment-button"
    :button-label="trans('plugins/ecommerce::payment.save')"
    size="md"
>
    {!! BaseHelper::clean(trans('plugins/ecommerce::customer.verify_email.confirm_description')) !!}
</x-core-base::modal>
