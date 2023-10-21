{!! Form::open(['url' => route('orders.update-tax-information', $tax->getKey())]) !!}
    <input type="hidden" name="order_id" value="{{ $orderId }}">

    <div class="next-form-section">
        <div class="next-form-grid">
            <div class="next-form-grid-cell">
                <label class="text-title-field required">{{ trans('plugins/ecommerce::order.tax_info.company_name') }}</label>
                <input type="text" class="next-input" name="company_name" placeholder="{{ trans('plugins/ecommerce::order.tax_info.company_name') }}" value="{{ $tax->company_name }}">
            </div>
            <div class="next-form-grid-cell">
                <label class="text-title-field">{{ trans('plugins/ecommerce::order.tax_info.company_email') }}</label>
                <input type="email" class="next-input" name="company_email" placeholder="{{ trans('plugins/ecommerce::order.tax_info.company_email') }}" value="{{ $tax->company_email }}">
            </div>
        </div>
        <div class="next-form-grid">
            <div class="next-form-grid-cell">
                <label class="text-title-field">{{ trans('plugins/ecommerce::order.tax_info.company_tax_code') }}</label>
                <input type="text" class="next-input" name="company_tax_code" placeholder="{{ trans('plugins/ecommerce::order.tax_info.company_tax_code') }}" value="{{ $tax->company_tax_code }}">
            </div>
        </div>

        <div class="next-form-grid">
            <div class="next-form-grid-cell">
                <label class="text-title-field required">{{ trans('plugins/ecommerce::order.tax_info.company_address') }}</label>
                <input type="text" class="next-input" name="company_address" placeholder="{{ trans('plugins/ecommerce::order.tax_info.company_address') }}" value="{{ $tax->company_address }}">
            </div>
        </div>
    </div>
{!! Form::close() !!}
