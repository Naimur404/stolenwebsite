@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        <div class="row">
            <div class="col-md-7 mb-3 mb-md-0">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex justify-content-between align-items-center mb-3">
                            @include('plugins/ecommerce::reviews.partials.rating', ['star' => $review->star])

                            {!! $review->status->toHtml() !!}
                        </div>

                        <p class="card-text">
                            {{ $review->comment }}
                        </p>

                        <div class="text-end">
                            <button class="btn btn-outline-danger btn-trigger-delete-review" data-target="{{ route('reviews.destroy', $review) }}">{{ trans('plugins/ecommerce::review.delete') }}</button>
                            @if($review->status == \Botble\Base\Enums\BaseStatusEnum::PUBLISHED)
                                <button class="btn btn-outline-warning btn-trigger-unpublish-review" data-id="{{ $review->getKey() }}">{{ trans('plugins/ecommerce::review.unpublish') }}</button>
                            @else
                                <button class="btn btn-outline-warning btn-trigger-publish-review" data-id="{{ $review->getKey() }}">{{ trans('plugins/ecommerce::review.publish') }}</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ $review->user->name }}
                        (<a href="mailto:{{ $review->user->email }}">{{ $review->user->email }}</a>)
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            {{ trans('plugins/ecommerce::review.product') }}
                        </h5>

                        <div class="d-flex gap-3 align-items-start">
                            <img src="{{ RvMedia::getImageUrl($review->product->image, 'thumb', false, RvMedia::getDefaultImage()) }}" alt="{{ $review->product->name }}" class="img-thumbnail" style="width: 15%">
                            <div>
                                <h5>
                                    <a href="{{ route('products.edit', $review->product) }}">
                                        {{ $review->product->name }}
                                    </a>
                                </h5>
                                <div>
                                    @include('plugins/ecommerce::reviews.partials.rating', ['star' => $review->product->reviews_avg_star])
                                    <span>({{ number_format($review->product->reviews_count) }})</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-core-base::modal
            id="delete-review-modal"
            :title="trans('plugins/ecommerce::review.delete_modal.title')"
            type="danger"
            button-id="confirm-delete-review-button"
            :button-label="trans('plugins/ecommerce::review.delete')"
        >
            {{ trans('plugins/ecommerce::review.delete_modal.description') }}
        </x-core-base::modal>
    </div>
@endsection
