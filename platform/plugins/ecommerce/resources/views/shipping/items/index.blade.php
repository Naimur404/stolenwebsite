@if (Auth::user()->hasAnyPermission(['ecommerce.shipping-rule-items.create', 'ecommerce.shipping-rule-items.bulk-import']))
    <div class="mt-3 text-end">
        @if (Auth::user()->hasPermission('ecommerce.shipping-rule-items.create'))
            <button type="button" class="btn btn-info btn-shipping-rule-item-trigger btn-sm"
                data-url="{{ route('ecommerce.shipping-rule-items.create', ['shipping_rule_id' => $rule->id]) }}">
                <i class="fa fa-add"></i>
                <span>{{ trans('core/base::forms.create') }}</span>
            </button>
        @endif
        @if (Auth::user()->hasPermission('ecommerce.shipping-rule-items.bulk-import'))
            <a class="btn btn-info btn-sm"
                href="{{ route('ecommerce.shipping-rule-items.bulk-import.index') }}">
                <i class="fas fa-file-import"></i>
                <span>{{ trans('plugins/ecommerce::bulk-import.tables.import') }}</span>
            </a>
        @endif
    </div>
@endif

@include('plugins/ecommerce::shipping.items.table')
