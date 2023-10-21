@php
    Theme::layout('full-width');

    $layout = request()->input('layout') ?: theme_option('store_list_layout');

    $layout = $layout && in_array($layout, array_keys(get_store_list_layouts())) ? $layout : 'grid';
@endphp

{!! Theme::partial('page-header', ['withTitle' => true]) !!}

<div class="container-xxxl mb-4">
    <div class="row">
        <div class="col-12">
            <div class="store-listing-filter-wrap">
                <div class="header-filter row g-0 bg-light border justify-content-between">
                    <div class="col-auto p-2 align-items-center d-flex">
                        <span class="ps-2 fs-6 text-gray">{{ __('Total stores showing: :number', ['number' => $stores->total()]) }}</span>
                    </div>
                    <div class="col-auto p-2">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <button class="store-list-filter-button btn btn-primary rounded-0 py-2 px-3">
                                    <span class="svg-icon">
                                        <svg>
                                            <use href="#svg-icon-filter" xlink:href="#svg-icon-filter"></use>
                                        </svg>
                                    </span>
                                    <span class="ms-2">{{ __('Filter') }}</span>
                                </button>
                            </div>
                            <div class="col-auto">
                                <div class="store-toolbar__view d-flex align-items-center">
                                    <div class="toolbar-view__icon">
                                        <a class="grid @if ($layout != 'list') active @endif" href="#"
                                            data-layout="grid"
                                            data-target=".store-listing-content"
                                            data-class-remove="row-cols-sm-2 row-cols-1 store-listing__list"
                                            data-class-add="row-cols-md-4 row-cols-sm-2 row-cols-1">
                                            <span class="svg-icon">
                                                <svg>
                                                    <use href="#svg-icon-grid" xlink:href="#svg-icon-grid"></use>
                                                </svg>
                                            </span>
                                        </a>
                                        <a class="list @if ($layout == 'list') active @endif" href="#"
                                            data-layout="list"
                                            data-target=".store-listing-content"
                                            data-class-add="row-cols-sm-2 row-cols-1 store-listing__list"
                                            data-class-remove="row-cols-md-4 row-cols-sm-2 row-cols-1">
                                            <span class="svg-icon">
                                                <svg>
                                                    <use href="#svg-icon-list" xlink:href="#svg-icon-list"></use>
                                                </svg>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form class="my-3" id="store-listing-filter-form-wrap" action="{{ route('public.stores') }}"
                method="GET" role="form" @if (!request()->has('q')) style="display: none" @endif >
                @foreach (request()->input() as $key => $item)
                    @if ($key != 'q')
                        <input type="hidden" name="{{ $key }}" value="{{ $item }}">
                    @endif
                @endforeach
                <div class="row g-0">
                    <div class="col-12 bg-light p-4 border">
                        <div class="store-search">
                            <input class="form-control" name="q" value="{{ BaseHelper::stringify(request()->query('q')) }}" type="search" placeholder="{{ __('Search store...') }}">
                        </div>
                        <div class="apply-filter row justify-content-end mt-2">
                            <div class="col-auto">
                                <button class="btn btn-primary px-4 py-2 border border-secondary" type="submit">{{ __('Apply') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="my-4">
                <div class="row @if ($layout == 'list') row-cols-sm-2 row-cols-1 store-listing__list @else row-cols-md-4 row-cols-sm-2 row-cols-1 @endif store-listing-content">
                    @if ($stores->count() && $stores->loadMissing('metadata'))
                        @foreach($stores as $store)
                            <div class="col my-2">
                                <div class="card store-card-wrapper h-100">
                                    <div class="card-header p-0 pt-3 pb-3 text-center">
                                        <a class="store-logo" href="{{ $store->url }}">
                                            <img class="lazyload" style="background-color: #fff; border-radius: 50%" data-src="{{ $store->logo_url }}" alt="{{ $store->name }}">
                                        </a>
                                    </div>
                                    <div class="card-body store-content bg-light">
                                        <div class="store-data-container row g-1">
                                            <div class="col-12 store-data">
                                                <div class="store-title d-flex align-items-center">
                                                    <h2 class="h5 mb-0">
                                                        <a href="{{ $store->url }}">{{ $store->name }}</a>
                                                    </h2>
                                                </div>
                                                @if (EcommerceHelper::isReviewEnabled())
                                                    <div class="mt-1">
                                                        {!! Theme::partial('star-rating', [
                                                            'avg'   => $store->reviews()->avg('star'),
                                                            'count' => $store->reviews()->count()
                                                        ]) !!}
                                                    </div>
                                                @endif
                                                <div class="vendor-store-address mt-3 mb-1">
                                                    <i class="icon icon-map-marker"></i>
                                                    {{ $store->full_address }}
                                                </div>
                                                @if (! MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
                                                    <div class="vendor-store-phone mb-1">
                                                        <i class="icon icon-telephone"></i> <a href="tel:{{ $store->phone }}">{{ $store->phone }}</a>
                                                    </div>
                                                @endif
                                                @if (! MarketplaceHelper::hideStoreEmail() && $store->email)
                                                    <div class="vendor-store-email mb-1">
                                                        <i class="icon icon-envelope"></i> <a href="mailto:{{ $store->email }}">{{ $store->email }}</a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer store-footer bg-light border-0">
                                        <div class="px-2 border-top visit-store-wrapper">
                                            <a class="mt-2 btn btn-secondary" href="{{ $store->url }}" title="{{ __('Visit Store') }}">
                                                <span class="svg-icon">
                                                    <svg>
                                                        <use href="#svg-icon-store" xlink:href="#svg-icon-store"></use>
                                                    </svg>
                                                </span> {{ __('Visit Store') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 w-100">
                            <div class="alert alert-warning">
                                {{ __('No vendor found.') }}
                            </div>
                        </div>
                    @endif
                </div>
                <div class="row mt-2 mb-3">
                    {!! $stores->withQueryString()->links(Theme::getThemeNamespace() . '::partials.pagination-numeric') !!}
                </div>
            </div>
        </div>
    </div>
</div>
