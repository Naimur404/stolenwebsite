<div class="star-rating-wrapper">
    <div class="star-rating" role="img" aria-label="Rated {{ $avg }} out of 5">
        <span class="max-rating rating-stars">
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
        </span>
        <span class="user-rating rating-stars" style="width: {{ $avg * 20 }}%">
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
            <span class="svg-icon">
                <svg>
                    <use href="#svg-icon-star" xlink:href="#svg-icon-star"></use>
                </svg>
            </span>
        </span>
    </div>
    @if (isset($count))
        <small class="star-count ms-1 text-secondary d-inline-block">
            (<span class="d-inline-block">{{ $count }}</span><span class="ms-1 star-customers-review">{{ __('customers review') }}</span>)
        </small>
    @endif
</div>
