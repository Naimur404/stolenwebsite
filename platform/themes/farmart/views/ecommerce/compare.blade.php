<div class="row compare-page-content py-5 mt-3">
        <div class="col-12">
            @if ($products->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" cellpadding="0" cellspacing="0" role="grid">
                        <thead>
                            <tr role="row" style="height: 0px;">
                                <th rowspan="1" colspan="1" style="padding-top: 0px; padding-bottom: 0px; border-top-width: 0px; border-bottom-width: 0px; height: 0px; width: 0px;"></th>
                                @foreach($products as $product)
                                    <td rowspan="1" colspan="1" style="padding-top: 0px; padding-bottom: 0px; border-top-width: 0px; border-bottom-width: 0px; height: 0px; width: 0px;"></td>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <tr class="d-none">
                                <th></th>
                                @foreach($products as $product)
                                    <td></td>
                                @endforeach
                            </tr>
                            <tr>
                                <th></th>
                                @foreach($products as $product)
                                    <td>
                                        <div style="max-width: 150px">
                                            <div class="img-fluid-eq">
                                                <div class="img-fluid-eq__dummy"></div>
                                                <div class="img-fluid-eq__wrap">
                                                    <img class="lazyload" src="{{ image_placeholder($product->image, 'thumb') }}"
                                                        data-src="{{ RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $product->name }}">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                @foreach($products as $product)
                                    <td>{{ $product->name }}</td>
                                @endforeach
                            </tr>
                            <tr class="price">
                                <th>{{ __('Price') }}</th>
                                @foreach($products as $product)
                                    <td>
                                        {!! Theme::partial('ecommerce.product-price', compact('product')) !!}
                                    </td>
                                @endforeach
                            </tr>
                            @if (EcommerceHelper::isCartEnabled())
                                <tr class="add-to-cart">
                                    <th>{{ __('Add to cart') }}</th>
                                    @foreach($products as $product)
                                    <td>
                                        {!! Theme::partial('ecommerce.product-cart-form', compact('product')) !!}
                                    </td>
                                    @endforeach
                                </tr>
                            @endif
                            <tr class="description">
                                <th>{{ __('Description') }}</th>
                                @foreach($products as $product)
                                    <td>
                                        {!! BaseHelper::clean($product->description) !!}
                                    </td>
                                @endforeach
                            </tr>
                            <tr class="sku">
                                <th>{{ __('SKU') }}</th>
                                @foreach($products as $product)
                                    <td>{{ $product->sku ? '#' . $product->sku : '' }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th>{{ __('Availability') }}</th>
                                @foreach($products as $product)
                                    <td>
                                        <div class="without-bg product-stock @if ($product->isOutOfStock()) out-of-stock @else in-stock @endif">
                                            @if ($product->isOutOfStock()) {{ __('Out of stock') }} @else {{ __('In stock') }} @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                            @foreach($attributeSets as $attributeSet)
                                @if ($attributeSet->is_comparable)
                                    <tr>
                                        <th class="heading">
                                            {{ $attributeSet->title }}
                                        </th>

                                        @foreach($products as $product)
                                            @php
                                                $attributes = app(\Botble\Ecommerce\Repositories\Interfaces\ProductInterface::class)->getRelatedProductAttributes($product)->where('attribute_set_id', $attributeSet->id)->sortBy('order');
                                            @endphp

                                            @if ($attributes->count())
                                                @if ($attributeSet->display_layout == 'dropdown')
                                                    <td>
                                                        {{ $attributes->pluck('title')->implode(', ') }}
                                                    </td>
                                                @elseif ($attributeSet->display_layout == 'text')
                                                    <td>
                                                        <div class="attribute-values">
                                                            <ul class="text-swatch attribute-swatch color-swatch">
                                                                @foreach($attributes as $attribute)
                                                                    <li class="attribute-swatch-item" style="display: inline-block">
                                                                        <label>
                                                                            <input class="form-control product-filter-item" type="radio" disabled>
                                                                            <span style="cursor: default">{{ $attribute->title }}</span>
                                                                        </label>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </td>
                                                @else
                                                    <td>
                                                        <div class="attribute-values">
                                                            <ul class="visual-swatch color-swatch attribute-swatch">
                                                            @foreach($attributes as $attribute)
                                                                <li class="attribute-swatch-item" style="display: inline-block">
                                                                    <div class="custom-radio">
                                                                        <label>
                                                                            <input class="form-control product-filter-item" type="radio" disabled>
                                                                            <span style="{{ $attribute->image ? 'background-image: url(' . RvMedia::getImageUrl($attribute->image) . ');' : 'background-color: ' . $attribute->color . ';' }}; cursor: default;"></span>
                                                                        </label>
                                                                    </div>
                                                                </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </td>
                                                @endif
                                            @else
                                                <td>&mdash;</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                            @endforeach

                            <tr>
                                <th></th>
                                @foreach($products as $product)
                                    <td>
                                        <button type="button" class="fs-4 remove btn remove-compare-item" href="#"
                                                data-url="{{ route('public.compare.remove', $product->id) }}"
                                                aria-label="{{ __('Remove this item') }}">
                                                <span class="svg-icon">
                                                    <svg>
                                                        <use href="#svg-icon-trash" xlink:href="#svg-icon-trash"></use>
                                                    </svg>
                                                </span>
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center">{{ __('No products in compare list!') }}</p>
            @endif
        </div>
    </div>
