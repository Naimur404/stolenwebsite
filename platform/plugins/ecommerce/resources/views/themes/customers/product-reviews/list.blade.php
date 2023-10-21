@extends(EcommerceHelper::viewPath('customers.master'))
@section('content')
    @include('plugins/ecommerce::themes.customers.product-reviews.icons')
    <div class="section-header">
        <h3>{{ SeoHelper::getTitle() }}</h3>
    </div>
    <div class="section-content product-reviews-page">
        <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link @if (! request()->has('page')) active @endif" id="waiting-tab"
                    data-toggle="tab" data-target="#waiting-tab-pane" data-bs-toggle="tab" data-bs-target="#waiting-tab-pane" type="button"
                    role="tab" aria-controls="waiting-tab-pane" aria-selected="true">{{ __('Waiting for your review') }} ({{ $products->count() }})</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link @if (request()->has('page')) active @endif" id="reviewed-tab"
                    data-toggle="tab" data-target="#reviewed-tab-pane"
                    data-bs-toggle="tab" data-bs-target="#reviewed-tab-pane" type="button"
                    role="tab" aria-controls="reviewed-tab-pane" aria-selected="false">{{ __('Reviewed') }} ({{ $reviews->total() }})</button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 p-2">
            <div class="tab-pane fade @if (! request()->has('page')) show active @endif" id="waiting-tab-pane" role="tabpanel" aria-labelledby="waiting-tab" tabindex="0">
                @if ($products->count())
                    <div class="row row-cols-md-3 row-cols-1 gx-2">
                        @foreach ($products as $product)
                            <div class="col mt-3 ecommerce-product-item" data-id="{{ $product->id }}">
                                <div class="card mb-3 p-1">
                                    <div class="row g-1">
                                        <div class="col-md-4">
                                            <img src="{{ RvMedia::getImageUrl($product->order_product_image ?: $product->image, 'thumb', false, RvMedia::getDefaultImage())}}"
                                                class="img-fluid rounded-start ecommerce-product-image" alt="{{ $product->name }}">
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="card-title ecommerce-product-name">{{ $product->order_product_name ?: $product->name }}</h6>
                                            @if ($product->order_completed_at)
                                                <div class="text-secondary">
                                                    <span>{{ __('Order completed at:') }}</span>
                                                    <time>{{ Carbon\Carbon::parse($product->order_completed_at)->translatedFormat('M d, Y h:m') }}</time>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex ecommerce-product-star">
                                                @for ($i = 5; $i >= 1; $i--)
                                                    <label class="order-{{ $i }}">
                                                        <span class="ecommerce-icon" data-star="{{ $i }}">
                                                            <svg> 
                                                                <use href="#ecommerce-icon-star" xlink:href="#ecommerce-icon-star"></use>
                                                            </svg>
                                                        </span>
                                                    </label>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        <span>{{ __('You do not have any products to review yet. Just shopping!') }}</span>
                    </div>
                @endif
            </div>
            <div class="tab-pane fade @if (request()->has('page')) show active @endif" id="reviewed-tab-pane" role="tabpanel" aria-labelledby="reviewed-tab" tabindex="0">
                @include('plugins/ecommerce::themes.customers.product-reviews.reviewed')
            </div>
        </div>
        
        @include('plugins/ecommerce::themes.customers.product-reviews.modal')
    </div>
    
@endsection
