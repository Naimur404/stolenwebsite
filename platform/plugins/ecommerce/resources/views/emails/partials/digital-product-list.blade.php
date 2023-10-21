<div class="table">
    <table>
        <tr>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.product_image') }}
            </th>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.product_name') }}
            </th>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.download') }}
            </th>
        </tr>

        @foreach ($order->digitalProducts() as $orderProduct)
            <tr>
                <td>
                    <img src="{{ RvMedia::getImageUrl($orderProduct->product_image, 'thumb') }}" alt="{{ $orderProduct->product_image }}" width="50">
                </td>
                <td>
                    <span>{{ $orderProduct->product_name }}</span>
                </td>
                <td>
                    @if ($orderProduct->product_file_internal_count)
                        <div>
                            <a href="{{ $orderProduct->download_hash_url }}">{{ __('All files') }}</a>
                        </div>
                    @endif
                    @if ($orderProduct->product_file_external_count)
                        <div>
                            <a href="{{ $orderProduct->download_external_url }}">{{ __('External link downloads') }}</a>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
    </table><br>
</div>

