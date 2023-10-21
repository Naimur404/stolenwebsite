<div class="ps-page--single ps-page--vendor">
    <section class="ps-store-list">
        <div class="container">
            <div class="ps-section__header">
                <h3>{{ __('Our Stores') }}</h3>
            </div>
            <div class="ps-section__content">
                <div class="ps-section__search row">
                    <div class="col-md-4">
                        <form action="{{ route('public.stores') }}" method="get">
                            <div class="form-group mb-3">
                                <button><i class="icon-magnifier"></i></button>
                                <input class="form-control" name="q" value="{{ BaseHelper::stringify(request()->input('q')) }}" type="text" placeholder="{{ __('Search vendor...') }}">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    @foreach($stores as $store)
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 ">
                            <article class="ps-block--store-2">
                                <div class="ps-block__content bg--cover">
                                    <figure>
                                        <h4>{{ $store->name }}</h4>
                                        @if (EcommerceHelper::isReviewEnabled())
                                            <div class="rating_wrap">
                                                <div class="rating">
                                                    <div class="product_rate" style="width: {{ $store->reviews->avg('star') * 20 }}%"></div>
                                                </div>
                                                <span class="rating_num">({{ $store->reviews->count() }})</span>
                                            </div>
                                        @endif
                                        <p>{{ $store->full_address }}</p>
                                        @if (!MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
                                            <p><i class="icon-telephone"></i> {{ $store->phone }}</p>
                                        @endif
                                        @if (!MarketplaceHelper::hideStoreEmail() && $store->email)
                                            <p><i class="icon-envelope"></i> <a href="mailto:{{ $store->email }}">{{ $store->email }}</a></p>
                                        @endif
                                    </figure>
                                </div>
                                <div class="ps-block__author">
                                    <a class="ps-block__user" href="{{ $store->url }}">
                                        <img src="{{ RvMedia::getImageUrl($store->logo, 'small', false, RvMedia::getDefaultImage()) }}" alt="{{ $store->name }}">
                                    </a>
                                    <a class="ps-btn" href="{{ $store->url }}">{{ __('Visit Store') }}</a>
                                </div>
                            </article>
                        </div>
                    @endforeach

                    <div class="ps-pagination pt-3 col-12">
                        {!! $stores->withQueryString()->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
