<div class="ps-page--single ps-page--vendor">
    <section class="ps-store-list">
        <div class="container">
            <aside class="ps-block--store-banner">
                <div class="ps-block__user">
                    <div class="ps-block__user-avatar">
                        <img src="{{ $store->logo_url }}" alt="{{ $store->name }}">
                        @if (EcommerceHelper::isReviewEnabled())
                            <div class="rating_wrap">
                                <div class="rating">
                                    <div class="product_rate" style="width: {{ $store->reviews()->avg('star') * 20 }}%"></div>
                                </div>
                                <span class="rating_num">({{ $store->reviews()->count() }})</span>
                            </div>
                        @endif
                    </div>
                    <div class="ps-block__user-content">
                        <h3 class="text-white">{{ $store->name }}</h3>
                        @if ($store->full_address)
                            <p><i class="icon-map-marker"></i>&nbsp;{{ $store->full_address }}</p>
                        @endif
                        @if (!MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
                            <p><i class="icon-telephone"></i>&nbsp;{{ $store->phone }}</p>
                        @endif
                        @if (!MarketplaceHelper::hideStoreEmail() && $store->email)
                            <p><i class="icon-envelope"></i>&nbsp;<a href="mailto:{{ $store->email }}">{{ $store->email }}</a></p>
                        @endif
                    </div>
                </div>
            </aside>
            <div class="ps-section__wrapper">
                <div class="ps-shopping ps-tab-root">
                    <div class="ps-shopping__header">
                        <p><strong> {{ $products->total() }}</strong> {{ __('Products found') }}</p>
                        <div class="ps-shopping__actions">
                            <div class="ps-shopping__view">
                                <p>{{ __('View') }}</p>
                                <ul class="ps-tab-list">
                                    <li class="active"><a href="#tab-1"><i class="icon-grid"></i></a></li>
                                    <li><a href="#tab-2"><i class="icon-list4"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="ps-tabs">
                        <div class="ps-tab active" id="tab-1">
                            <div class="ps-shopping-product">
                                <div class="row">
                                    @if ($products->count() > 0)
                                        @foreach($products as $product)
                                            <div class="col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 ">
                                                <div class="ps-product">
                                                    {!! Theme::partial('ecommerce.product-item', compact('product')) !!}
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="ps-pagination">
                                {!! $products->withQueryString()->links() !!}
                            </div>
                        </div>
                        <div class="ps-tab" id="tab-2">
                            @include('plugins/marketplace::themes.stores.items')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
