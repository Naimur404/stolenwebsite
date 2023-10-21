@php Theme::layout('full-width'); @endphp

{!! Theme::partial('page-header') !!}

<div class="container-xxxl">
    <div class="row mt-5">
        <div class="col-xl-3 col-lg-4">
            <aside class="catalog-primary-sidebar catalog-sidebar" data-toggle-target="product-categories-primary-sidebar">
                <div class="backdrop"></div>
                <div class="catalog-sidebar--inner side-left">
                    <div class="panel__header d-lg-none mb-4">
                        <span class="panel__header-title">{{ __('Filter Products') }}</span>
                        <a class="close-toggle--sidebar" href="#" data-toggle-closest=".catalog-primary-sidebar">
                            <span class="svg-icon">
                                <svg>
                                    <use href="#svg-icon-arrow-right" xlink:href="#svg-icon-arrow-right"></use>
                                </svg>
                            </span>
                        </a>
                    </div>
                    @php
                        $categories = ProductCategoryHelper::getActiveTreeCategories();
                        $categoriesRequest = (array)request()->input('categories', []);
                        $urlCurrent = URL::current();
                        $activeCategoryId = Arr::get($categoriesRequest, 0);
                    @endphp
                    <div class="catalog-filter-sidebar-content px-3 px-md-0">
                        <form action="{{ URL::current() }}"
                            data-action="{{ $store->url }}"
                            method="GET"
                            id="products-filter-form"
                            data-title="{{ $store->name }}">
                            <input type="hidden" name="sort-by" class="product-filter-item" value="{{ BaseHelper::stringify(request()->input('sort-by')) }}">
                            <input type="hidden" name="layout" class="product-filter-item" value="{{ BaseHelper::stringify(request()->input('layout')) }}">
                            <div class="widget-wrapper widget-product-categories">
                                <h4 class="widget-title">{{ __('All Categories') }}</h4>
                                <input type="hidden" name="categories[]" value="{{ $activeCategoryId }}" class="product-filter-item">
                                <div class="widget-layered-nav-list">
                                    @include(Theme::getThemeNamespace('views.ecommerce.includes.categories'), compact('categories', 'categoriesRequest', 'urlCurrent', 'activeCategoryId'))
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </aside>
            <aside class="catalog-primary-sidebar catalog-sidebar" data-toggle-target="contact-store-primary-sidebar">
                <div class="backdrop"></div>
                <div class="catalog-sidebar--inner side-left">
                    <div class="panel__header d-lg-none mb-4">
                        <span class="panel__header-title">{{ __('Contact Vendor') }}</span>
                        <a class="close-toggle--sidebar" href="#" data-toggle-closest=".catalog-primary-sidebar">
                            <span class="svg-icon">
                                <svg>
                                    <use href="#svg-icon-arrow-right" xlink:href="#svg-icon-arrow-right"></use>
                                </svg>
                            </span>
                        </a>
                    </div>

                    <div class="catalog-filter-sidebar-content px-3 px-md-0">
                        <div class="widget-wrapper widget-contact-store">
                            <h4 class="widget-title">{{ __('Contact Vendor') }}</h4>
                            <form class="form-contact-store" action="{{ route('public.ajax.contact-seller') }}" method="post">
                                @csrf
                                <div class="mb-3">
                                    <input class="form-control" type="text" name="name" @if (auth('customer')->check()) value="{{ auth('customer')->user()->name }}" @endif
                                    placeholder="{{ __('Your Name') }}" @if (auth('customer')->check()) disabled @else minlength="5" required="required" @endif>
                                </div>
                                <div class="mb-3">
                                    <input class="form-control" type="email" name="email" @if (auth('customer')->check()) value="{{ auth('customer')->user()->email }}" @endif
                                    placeholder="you@example.com" @if (auth('customer')->check()) disabled @else required="required" @endif>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" name="content" maxlength="5000" cols="25"
                                        rows="6" placeholder="{{ __('Type your message...') }}"
                                        required="required"></textarea>
                                </div>
                                <div class="d-grid">
                                    <button class="btn btn-primary" type="submit">{{ __('Send Message') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
        <div class="col-xl-9 col-lg-8">
            @include(Theme::getThemeNamespace() . '::views.marketplace.includes.info-box', ['showContactVendor' => true])
            <div class="row justify-content-center my-5 mb-2">
                <div class="col-12">
                    <div class="form-group">
                        <form action="{{ URL::current() }}" method="GET" class="products-filter-form-vendor">
                            <div class="input-group">
                                <input type="text" class="form-control" name="q" value="{{ BaseHelper::stringify(request()->query('q')) }}" form="products-filter-form" placeholder="{{ __('Search in this store...') }}">
                                <button type="submit" class="btn btn-primary px-3 justify-content-center">
                                    <span class="svg-icon me-2 d-block text-center w-100">
                                        <svg>
                                            <use href="#svg-icon-search" xlink:href="#svg-icon-search"></use>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="bg-light p-2 my-3">
                <div class="row catalog-header justify-content-between">
                    <div class="col-auto catalog-header__left d-flex align-items-center">
                        <h2 class="h6 catalog-header__title d-none d-lg-block mb-0 ps-2">
                            <span class="products-found">
                                <span class="text-primary me-1">{{ $products->total() }}</span>{{ __('Products found') }}
                            </span>
                        </h2>
                        <a class="d-lg-none sidebar-filter-mobile" href="#" data-toggle="product-categories-primary-sidebar">
                            <span class="svg-icon me-2">
                                <svg>
                                    <use href="#svg-icon-filter" xlink:href="#svg-icon-filter"></use>
                                </svg>
                            </span>
                            <span>{{ __('Filter') }}</span>
                        </a>
                    </div>
                    <div class="col-auto catalog-header__right">
                        <div class="catalog-toolbar row align-items-center">
                            @include(Theme::getThemeNamespace() . '::views.ecommerce.includes.layout')
                        </div>
                    </div>
                </div>
            </div>
            <div class="products-listing position-relative">
                @include(Theme::getThemeNamespace('views.marketplace.stores.items'))
            </div>
        </div>
    </div>
</div>
