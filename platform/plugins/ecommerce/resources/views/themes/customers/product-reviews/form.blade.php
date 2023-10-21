{!! Form::open([
    'route' => 'public.reviews.create',
    'method' => 'POST',
    'class' => 'ecommerce-form-review-product',
    'files' => true,
]) !!}
    <input type="hidden" name="product_id" value="{{ $product ? $product->id : '' }}">
    <div class="row">
        <div class="col-3">
            <img src="{{ $product ? RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) : '' }}" alt="product-image" class="ecommerce-product-image">
        </div>
        <div class="col-9">
            <h2 class="modal-title fs-5 ecommerce-product-name" id="product-review-modal-label">{{ $product ? $product->name : '' }}</h2>
            <div class="col-12 mb-3 d-flex mt-2">
                <div class="ecommerce-form-rating-stars ms-2">
                    @for ($i = 5; $i >= 1; $i--)
                        <input class="btn-check" type="radio" required id="rating-star-{{ $i }}" name="star" value="{{ $i }}">
                        <label for="rating-star-{{ $i }}" title="{{ $i }} stars">
                            <span class="ecommerce-icon">
                                <svg>
                                    <use href="#ecommerce-icon-star-o" xlink:href="#ecommerce-icon-star-o"></use>
                                </svg>
                            </span>
                        </label>
                    @endfor
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mb-3">
            <label class="required" for="txt-comment">{{ __('Review') }}:</label>
            <textarea class="form-control" name="comment" id="txt-comment" required aria-required="true"
                rows="5" placeholder="{{ __('Write your review') }}"></textarea>
        </div>
        <div class="col-12 mb-3 form-group">
            <script type="text/x-custom-template" id="ecommerce-review-image-template">
                <span class="ecommerce-image-viewer__item" data-id="__id__">
                    <img src="{{ RvMedia::getDefaultImage() }}" alt="Preview" class="img-responsive d-block">
                    <span class="image-viewer__icon-remove">
                        <span class="ecommerce-icon">
                            <svg>
                                <use href="#ecommerce-icon-cross" xlink:href="#ecommerce-icon-cross"></use>
                            </svg>
                        </span>
                    </span>
                </span>
            </script>
            <div class="ecommerce-image-upload__viewer d-flex">
                <div class="ecommerce-image-viewer__list position-relative">
                    <div class="ecommerce-image-upload__uploader-container">
                        <div class="d-table">
                            <div class="ecommerce-image-upload__uploader">
                                <span class="ecommerce-icon ecommerce-image-upload__icon">
                                    <svg>
                                        <use href="#ecommerce-icon-image" xlink:href="#ecommerce-icon-image"></use>
                                    </svg>
                                </span>
                                <div class="ecommerce-image-upload__text">{{ __('Upload photos') }}</div>
                                <input type="file"
                                    name="images[]"
                                    data-max-files="{{ EcommerceHelper::reviewMaxFileNumber() }}"
                                    class="ecommerce-image-upload__file-input"
                                    accept="image/png,image/jpeg,image/jpg"
                                    multiple="multiple"
                                    data-max-size="{{ EcommerceHelper::reviewMaxFileSize(true) }}"
                                    data-max-size-message="{{ trans('validation.max.file', ['attribute' => '__attribute__', 'max' => '__max__']) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="help-block">
                {{ __('You can upload up to :total photos, each photo maximum size is :max kilobytes', [
                        'total' => EcommerceHelper::reviewMaxFileNumber(),
                        'max' => EcommerceHelper::reviewMaxFileSize(true),
                    ]) }}
            </div>
        </div>
        <div class="col-12">
            <div class="alert alert-warning alert-message d-none"></div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary px-3" type="submit">
                <span class="ecommerce-icon">
                    <svg>
                        <use href="#ecommerce-icon-send" xlink:href="#ecommerce-icon-send"></use>
                    </svg>
                </span>
                <span>{{ __('Submit Review') }}</span>
            </button>
        </div>
    </div>
    <div class="comment-notes mt-3">
        <ul>
            <li>
                <span>{{ __('Your email address will not be published.') }}</span>
            </li>
            <li>
                <span class="required">{{ __('Required fields are marked') }}</span>
            </li>
        </ul>
    </div>
{!! Form::close() !!}
