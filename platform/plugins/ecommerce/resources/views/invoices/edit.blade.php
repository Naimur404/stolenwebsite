@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1036">
    <div class="card">
        <div class="card-body">
            <div class="invoice-info">
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            @if ($invoice->company_logo)
                                <img src="{{ RvMedia::getImageUrl($invoice->company_logo) }}" style="max-height: 150px;" alt="{{ $invoice->company_name }}">
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <h2 class="mb-0 uppercase">{{ trans('plugins/ecommerce::invoice.heading') }}</h2>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6 text-end">
                            <ul class="mb-0">
                                @if ($invoice->customer_name)
                                    <li>{{ $invoice->customer_name }}</li>
                                @endif
                                @if ($invoice->customer_email)
                                    <li>{{ $invoice->customer_email }}</li>
                                @endif
                                @if ($invoice->customer_phone)
                                    <li>{{ $invoice->customer_phone }}</li>
                                @endif
                                @if ($invoice->customer_address)
                                    <li>{{ $invoice->customer_address }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <hr>
                    </div>
                    <div class="col-lg-4">
                        <strong class="text-brand">{{ trans('plugins/ecommerce::invoice.detail.code') }}:</strong>
                        {{ $invoice->code }}
                    </div>
                    @if ($invoice->created_at)
                        <div class="col-lg-4">
                            <strong class="text-brand">{{ trans('plugins/ecommerce::invoice.detail.issue_at') }}:</strong>
                            {{ $invoice->created_at->translatedFormat('j F, Y') }}
                        </div>
                    @endif
                    @if ($invoice->payment->payment_channel->label())
                        <div class="col-lg-4">
                            <strong class="text-brand">{{ trans('plugins/ecommerce::invoice.payment_method') }}:</strong>
                            {{ $invoice->payment->payment_channel->label() }}
                        </div>
                    @endif
                    <div class="col-12">
                        <hr>
                    </div>
                </div>
                <table class="table table-striped mb-3">
                    <thead>
                    <tr>
                        <th>{{ trans('plugins/ecommerce::invoice.detail.description') }}</th>
                        <th>{{ trans('plugins/ecommerce::invoice.detail.qty') }}</th>
                        <th class="text-center">{{ trans('plugins/ecommerce::invoice.total_amount') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td style="width: 70%">
                                <p class="mb-0">{{ $item->name }}</p>
                                @if ($item->description)
                                    <small>{{ $item->description }}</small>
                                @endif
                            </td>
                            <td style="width: 5%">{{ $item->qty }}</td>
                            <td style="width: 25%" class="text-center">{{ format_price($item->amount) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">{{ trans('plugins/ecommerce::invoice.detail.quantity') }}:
                        </th>
                        <th class="text-center">{{ number_format($invoice->items->sum('qty')) }}</th>
                    </tr>
                    <tr>
                        <th colspan="2" class="text-end">{{ trans('plugins/ecommerce::invoice.detail.sub_total') }}:
                        </th>
                        <th class="text-center">{{ format_price($invoice->sub_total) }}</th>
                    </tr>
                    @if ($invoice->tax_amount > 0)
                        <tr>
                            <th colspan="2" class="text-end">{{ trans('plugins/ecommerce::invoice.detail.tax') }}:</th>
                            <th class="text-center">{{ format_price($invoice->tax_amount) }}</th>
                        </tr>
                    @endif
                    <tr>
                        <th colspan="2"
                            class="text-end">{{ trans('plugins/ecommerce::invoice.detail.shipping_fee') }}:
                        </th>
                        <th class="text-center">{{ format_price($invoice->shipping_amount) }}</th>
                    </tr>
                    <tr>
                        <th colspan="2" class="text-end">{{ trans('plugins/ecommerce::invoice.detail.discount') }}:
                        </th>
                        <th class="text-center">{{ format_price($invoice->discount_amount) }}</th>
                    </tr>
                    <tr>
                        <th colspan="2" class="text-end">{{ trans('plugins/ecommerce::invoice.detail.grand_total') }}:
                        </th>
                        <th class="text-center">{{ format_price($invoice->amount) }}</th>
                    </tr>
                    </tfoot>
                </table>
                <div class="row">
                    <div class="col-md-6">
                        <h5>{{ trans('plugins/ecommerce::invoice.detail.invoice_for') }}</h5>
                        <p class="font-sm">
                            @if ($invoice->created_at)
                                <strong>{{ trans('plugins/ecommerce::invoice.detail.issue_at') }}:</strong> {{ $invoice->created_at->format('j F, Y') }}<br>
                            @endif

                            @if ($invoice->company_name)
                                <strong>{{ trans('plugins/ecommerce::invoice.detail.invoice_to') }}:</strong> {{ $invoice->company_name }}<br>
                            @endif

                            @if ($invoice->customer_tax_id)
                                <strong>{{ trans('plugins/ecommerce::invoice.detail.tax_id') }}:</strong> {{ $invoice->customer_tax_id }}<br>
                            @endif

                            {!! apply_filters('ecommerce_admin_invoice_extra_info', null, $invoice->reference) !!}
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h5>{{ trans('plugins/ecommerce::invoice.total_amount') }}</h5>
                        <h3 class="mt-0 mb-0 text-danger">{{ format_price($invoice->amount) }}</h3>
                    </div>
                </div>
                <hr>
            </div>
        </div>
        <div class="card-footer text-center">
            <a href="{{ route('ecommerce.invoice.generate-invoice', ['invoice' => $invoice, 'type' => 'print']) }}" target="_blank"
               class="btn btn-danger">
                <i class="fas fa-print"></i> {{ trans('plugins/ecommerce::invoice.print') }}
            </a>
            <a href="{{ route('ecommerce.invoice.generate-invoice', ['invoice' => $invoice, 'type' => 'download']) }}"
               target="_blank" class="btn btn-success">
                <i class="fas fa-download"></i> {{ trans('plugins/ecommerce::invoice.download') }}
            </a>
        </div>
    </div>
    </div>
@endsection
