<tr class="shipping-rule-item-{{ $item->id }}">
    <th scope="row">{{ $item->id }}</th>
    <td>{{ $item->state_name }}</td>
    <td>{{ $item->city_name }}</td>
    <td>{{ $item->zip_code }}</td>
    <td>
        {{ ($item->adjustment_price < 0 ? '-' : '') . format_price($item->adjustment_price) }}
        {!! Html::tag('small', '(' . format_price(max($item->adjustment_price + $item->shippingRule->price, 0)) . ')', ['class' => 'text-info ms-1']) !!}
    </td>
    <td>
        @if ($item->is_enabled)
            {!! Html::tag('span', trans('core/base::base.yes'), ['class' => 'text-primary']) !!}
        @else
            {!! Html::tag('span', trans('core/base::base.no'), ['class' => 'text-secondary']) !!}
        @endif
    </td>
    <td>{{ BaseHelper::formatDate($item->created_at) }}</td>
    @if ($hasOperations)
        <td class="text-center">
            @if (Auth::user()->hasPermission('ecommerce.shipping-rule-items.edit'))
                <button class="btn btn-icon btn-sm btn-primary px-2 py-1 btn-shipping-rule-item-trigger" type="button"
                    data-url="{{ route('ecommerce.shipping-rule-items.edit', $item->id) }}">
                    <i class="fa fa-edit small"></i>
                </button>
            @endif

            @if (Auth::user()->hasPermission('ecommerce.shipping-rule-items.destroy'))
                <button type="button" class="btn btn-icon btn-sm btn-danger px-2 py-1 btn-confirm-delete-rule-item-modal-trigger"
                    data-section="{{ route('ecommerce.shipping-rule-items.destroy', $item->id) }}"
                    data-name="{{ $item->name_item }}">
                    <i class="fa fa-trash small"></i>
                </button>
            @endif
        </td>
    @endif
</tr>
