<div class="table-shipping-rule-items table-responsive" data-url="{{ route('ecommerce.shipping-rule-items.items', $rule->id) }}">
    <table class="table table-striped table-bordered mt-2 table-shipping-rule-{{ $rule->id }}">
        @php
            $orderBy = request()->input('order_by');
            $orderDir = request()->input('order_dir');
            $columns = [
                'id' => [
                    'title' => '#',
                    'width' => 0,
                ],
                'state' => [
                    'title' => trans('plugins/ecommerce::shipping.rule.item.tables.state'),
                ],
                'city' => [
                    'title' => trans('plugins/ecommerce::shipping.rule.item.tables.city'),
                ],
                'zip_code' => [
                    'title' => trans('plugins/ecommerce::shipping.rule.item.tables.zip_code'),
                ],
                'adjustment_price' => [
                    'title' => trans('plugins/ecommerce::shipping.rule.item.tables.adjustment_price'),
                ],
                'is_enabled' => [
                    'title' => trans('plugins/ecommerce::shipping.rule.item.tables.is_enabled'),
                ],
                'created_at' => [
                    'title' => trans('core/base::tables.created_at'),
                    'width' => '100',
                ],
                'operations' => [
                    'title' => trans('core/base::tables.operations'),
                    'width' => '120',
                    'class' => 'text-center',
                    'order' => false,
                ],
            ];
            $hasOperations = Auth::user()->hasAnyPermission(['ecommerce.shipping-rule-items.edit', 'ecommerce.shipping-rule-items.destroy']);
            if (! $hasOperations) {
                Arr::forget($columns, 'operations');
            }
        @endphp
        <thead>
            <tr>
                @foreach ($columns as $key => $column)
                    <th scope="col"
                        class="{{ Arr::get($column, 'class') }}"
                        width="{{ Arr::get($column, 'width') }}"
                        @if (Arr::get($column, 'order', true))
                            data-column="{{ $key }}"
                            data-dir="{{ $orderBy == $key ? ($orderDir == 'DESC' ? 'DESC' : 'ASC')  : '' }}"
                        @endif>
                        <span>{{ Arr::get($column, 'title') }}</span>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if (! empty($total))
                @forelse ($items ?? [] as $item)
                    @include('plugins/ecommerce::shipping.items.table-item')
                @empty
                    <tr>
                        <td colspan="100%" class="text-center">
                            <a class="d-block py-4 shipping-rule-load-items"
                                href="{{ route('ecommerce.shipping-rule-items.items', $rule->id) }}" class="p-3">
                                <span>{{ trans('plugins/ecommerce::shipping.rule.item.load_data_table', ['total' => $total]) }}</span>
                            </a>
                        </td>
                    </tr>
                @endforelse
            @endif
            <tr class="tr-no-data">
                <td colspan="100%">
                    <div class="dashboard_widget_msg">
                        <p class="smiley" aria-hidden="true"></p>
                        <p>{{ $message ?? trans('core/base::tables.no_data') }}</p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    @if (! empty($items) && $items instanceof Illuminate\Pagination\LengthAwarePaginator && $items->withQueryString() && $limit = $items->perPage())
        <div class="row g-0 mt-3">
            <div class="col-auto">
                <div class="number-record">
                    <input type="number" class="form-control numb pe-1" value="{{ $limit }}" step="5" min="5" max="{{ $items->total() }}">
                </div>
            </div>
            <div class="col">
                <div class="d-flex justify-content-end ">
                    {!! $items->links('plugins/ecommerce::shipping.items.pagination') !!}
                </div>
            </div>
        </div>
    @endif
</div>
