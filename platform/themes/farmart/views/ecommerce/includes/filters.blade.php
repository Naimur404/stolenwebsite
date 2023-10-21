@php
    [$categories, $brands, $tags, $rand, $categoriesRequest, $urlCurrent, $categoryId, $maxFilterPrice] = EcommerceHelper::dataForFilter($category ?? null);

    Theme::asset()->usePath()
                ->add('custom-scrollbar-css', 'plugins/mcustom-scrollbar/jquery.mCustomScrollbar.css');
    Theme::asset()->container('footer')->usePath()
                ->add('custom-scrollbar-js', 'plugins/mcustom-scrollbar/jquery.mCustomScrollbar.js', ['jquery']);
@endphp

<input type="hidden" name="sort-by" class="product-filter-item" value="{{ BaseHelper::stringify(request()->input('sort-by')) }}">
<input type="hidden" name="layout" class="product-filter-item" value="{{ BaseHelper::stringify(request()->input('layout')) }}">

<aside class="catalog-primary-sidebar catalog-sidebar" data-toggle-target="product-categories-primary-sidebar">
    <div class="backdrop"></div>
    <div class="catalog-sidebar--inner side-left">
        <div class="panel__header d-md-none mb-4">
            <span class="panel__header-title">{{ __('Filter Products') }}</span>
            <a class="close-toggle--sidebar" href="#" data-toggle-closest=".catalog-primary-sidebar">
                <span class="svg-icon">
                    <svg>
                        <use href="#svg-icon-arrow-right" xlink:href="#svg-icon-arrow-right"></use>
                    </svg>
                </span>
            </a>
        </div>
        <div class="catalog-filter-sidebar-content px-3 px-md-0">
            <div class="widget-wrapper widget-product-categories">
                <h4 class="widget-title">{{ __('Product Categories') }}</h4>
                <div class="widget-layered-nav-list">
                    @include(Theme::getThemeNamespace('views.ecommerce.includes.categories'), compact('categories', 'categoriesRequest', 'urlCurrent'))
                </div>
            </div>
            @if ($brands->isNotEmpty())
                <div @class(['widget-wrapper widget-product-brands'])>
                    <h4 class="widget-title">{{ __('Brands') }}</h4>
                    <div class="widget-layered-nav-list ps-custom-scrollbar">
                        <ul>
                            @foreach($brands as $brand)
                                <li data-id="{{ $brand->id }}"
                                    @class(['d-none' => $categoryId && $brand->categories->count() && ! $brand->categories->contains('id', $categoryId)])
                                    data-categories="{{ $brand->categories->pluck('id')->toJson() }}">
                                    <div class="widget-layered-nav-list__item">
                                        <div class="form-check">
                                            <input class="form-check-input product-filter-item" type="checkbox"
                                                name="brands[]" value="{{ $brand->id }}"
                                                id="attribute-brand-{{ $rand }}-{{ $brand->id }}"
                                                @checked(in_array($brand->id, request()->input('brands', [])))>
                                            <label class="form-check-label" for="attribute-brand-{{ $rand }}-{{ $brand->id }}">
                                                <span>{{ $brand->name }}</span>
                                                <span class="count">({{ $brand->products_count }})</span>
                                            </label>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if ($tags->isNotEmpty())
                <div class="widget-wrapper widget-product-tags">
                    <h4 class="widget-title">{{ __('Tags') }}</h4>
                    <div class="widget-layered-nav-list ps-custom-scrollbar">
                        <ul>
                            @foreach($tags as $tag)
                                <li>
                                    <div class="widget-layered-nav-list__item">
                                        <div class="form-check">
                                            <input class="form-check-input product-filter-item" type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                                id="attribute-tag-{{ $rand }}-{{ $tag->id }}"
                                                @checked(in_array($tag->id, request()->input('tags', [])))>
                                            <label class="form-check-label" for="attribute-tag-{{ $rand }}-{{ $tag->id }}">
                                                <span>{{ $tag->name }}</span>
                                                <span class="count">({{ $tag->products_count }})</span>
                                            </label>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if ($maxFilterPrice)
                <div class="widget-wrapper">
                    <h4 class="widget-title">{{ __('By Price') }}</h4>
                    <div class="widget__content nonlinear-wrapper">
                        <div class="nonlinear" data-min="0" data-max="{{ $maxFilterPrice }}"></div>
                        <div class="slider__meta">
                            <input class="product-filter-item product-filter-item-price-0" name="min_price" data-min="0"
                                   value="{{ BaseHelper::stringify(request()->input('min_price', 0)) }}" type="hidden">
                            <input class="product-filter-item product-filter-item-price-1" name="max_price" data-max="{{ $maxFilterPrice }}"
                                   value="{{ BaseHelper::stringify(request()->input('max_price', $maxFilterPrice)) }}" type="hidden">
                            <span class="slider__value me-2">
                                <span class="slider__min me-1"></span>
                                <span>{{ $currencyTitle = get_application_currency()->title }}</span>
                            </span>
                            <span>-</span>
                            <span class="slider__value ms-2">
                            <span class="slider__max me-1"></span>
                            <span>{{ $currencyTitle }}</span>
                        </span>
                        </div>
                    </div>

                    {!! render_product_swatches_filter([
                        'view' => Theme::getThemeNamespace('views.ecommerce.attributes.attributes-filter-renderer'),
                        'categoryId' => $categoryId,
                    ]) !!}
                </div>
            @endif
        </div>
    </div>
</aside>
