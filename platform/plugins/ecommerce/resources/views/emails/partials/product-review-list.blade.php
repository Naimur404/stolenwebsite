<div class="table">
    <table>
        <tr>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.product_image') }}
            </th>
            <th style="text-align: left">
                {{ trans('plugins/ecommerce::products.form.product') }}
            </th>
        </tr>

        @foreach ($products as $product)
            <tr>
                <td>
                    <img src="{{ RvMedia::getImageUrl($product->image, 'thumb') }}" alt="{{ $product->name }}" width="50">
                </td>
                <td>
                    <a href="{{ route('public.product.review', $product->slug) }}">{{ $product->name }}</a>
                </td>
            </tr>
        @endforeach
    </table><br>
</div>

