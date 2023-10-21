@if (!$productVariations->isEmpty())
    <table class="table table-hover-variants">
        <thead>
            <tr>
                <th style="width: 20px;">
                    <input class="table-check-all" data-set=".table-hover-variants .checkboxes" type="checkbox">
                </th>
                <th>{{ trans('plugins/ecommerce::products.form.image') }}</th>
                @foreach ($productAttributeSets->where('is_selected', '<>', null)->whereIn('id', $productVariationsInfo->pluck('attribute_set_id')->all())->sortBy('id') as $attributeSet)
                    <th>{{ $attributeSet->title }}</th>
                @endforeach
                @foreach ($productAttributeSets->where('is_selected', '<>', null)->whereNotIn('id', $productVariationsInfo->pluck('attribute_set_id')->all())->sortBy('id') as $attributeSet)
                    <th>{{ $attributeSet->title }}</th>
                @endforeach
                <th>{{ trans('plugins/ecommerce::products.form.price') }}</th>
                <th>{{ trans('plugins/ecommerce::products.form.is_default') }}</th>
                @if (EcommerceHelper::isEnabledSupportDigitalProducts() && $product && $product->isTypeDigital())
                    <th>{{ $product->product_type->label() }}</th>
                @endif
                <th class="text-center">{{ trans('plugins/ecommerce::products.form.action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productVariations as $variation)
                @php
                    $currentRelatedProduct = $productsRelatedToVariation->where('variation_id', $variation->id)->first();
                @endphp
                <tr id="variation-id-{{ $variation->id }}">
                    <td>
                        <input type="checkbox" class="checkboxes m-0" name="id[]" value="{{ $variation->id }}">
                    </td>
                    <td>
                        <div class="wrap-img-product">
                            <img src="{{ RvMedia::getImageUrl($currentRelatedProduct && $currentRelatedProduct->image ? $currentRelatedProduct->image : $product->image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ trans('plugins/ecommerce::products.form.image')  }}">
                        </div>
                    </td>
                    @foreach ($productVariationsInfo->where('variation_id', $variation->id)->sortBy('attribute_set_id') as $key => $item)
                        <td>{{ $item->title }}</td>
                    @endforeach
                    @for($index = 0; $index < ($productAttributeSets->where('is_selected', '<>', null)->count() - $productVariationsInfo->where('variation_id', $variation->id)->count()); $index++)
                        <td>--</td>
                    @endfor
                    <td>
                        @if ($currentRelatedProduct)
                            {{ format_price($currentRelatedProduct->front_sale_price) }}
                            @if ($currentRelatedProduct->front_sale_price != $currentRelatedProduct->price)
                                <del class="text-danger">{{ format_price($currentRelatedProduct->price) }}</del>
                            @endif
                        @else
                            {{ format_price($product->front_sale_price) }}
                            @if ($product->front_sale_price != $product->price)
                                <del class="text-danger">{{ format_price($product->price) }}</del>
                            @endif
                        @endif
                    </td>
                    <td>
                        <label>
                            <input type="radio"
                                @checked($variation->is_default)
                                name="variation_default_id"
                                value="{{ $variation->id }}">
                        </label>
                    </td>
                    @if (EcommerceHelper::isEnabledSupportDigitalProducts() && $product && $product->isTypeDigital())
                        <td>
                            @if ($currentRelatedProduct)
                                <span>{{ $currentRelatedProduct->productFiles->count() }}</span>
                                <span><i class="fas fa-paperclip"></i></span>
                            @endif
                        </td>
                    @endif
                    <td style="width: 180px;" class="text-center">
                        <a href="#" class="btn btn-info btn-trigger-edit-product-version"
                            data-target="{{ route('products.update-version', $variation->id) }}"
                            data-load-form="{{ route('products.get-version-form', $variation->id) }}">{{ trans('plugins/ecommerce::products.edit_variation_item') }}</a>
                        <a href="#" data-target="{{ route('products.delete-version', $variation->id) }}" data-id="{{ $variation->id }}"
                            class="btn-trigger-delete-version btn btn-danger">{{ trans('plugins/ecommerce::products.delete') }}</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>{{ trans('plugins/ecommerce::products.variations_box_description') }}</p>
@endif
