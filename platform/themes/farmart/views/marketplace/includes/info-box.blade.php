<div class="vendor-info-box">
    <div class="vendor-info-summary-wrapper">
        <div class="vendor-info-summary">
            @php $coverImage = $store->getMetadata('background', true); @endphp
            <div class="vendor-info"
            @if ($coverImage) style="background-image: url({{ RvMedia::getImageUrl($coverImage) }}); background-repeat: no-repeat;
                background-size: cover;
                background-position: center;" @endif>
                <div @if ($coverImage) style="background: rgba(0, 0, 0, 0.3)" @endif class="py-3">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="vendor-info-content px-3">
                                <div class="vendor-store-information row align-items-center">
                                    <div class="vendor-avatar col-3">
                                        <img class="rounded-circle" src="{{ $store->logo_url }}" alt="avatar">
                                    </div>
                                    <div class="vendor-store-info col">
                                        <h4 class="vendor-name">{{ $store->name }}</h4>
                                        @if (EcommerceHelper::isReviewEnabled())
                                            <div class="vendor-store-rating mb-3">
                                                {!! Theme::partial('star-rating', ['avg' => $store->reviews()->avg('star'), 'count' => $store->reviews()->count()]) !!}
                                            </div>
                                        @endif

                                        @if ($store->full_address)
                                            <div class="vendor-store-address mb-1">
                                                <i class="icon icon-map-marker"></i>&nbsp;{{ $store->full_address }}
                                            </div>
                                        @endif
                                        @if (! MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
                                            <div class="vendor-store-phone mb-1">
                                                <i class="icon icon-telephone"></i>&nbsp;<a href="tel:{{ $store->phone }}">{{ $store->phone }}</a>
                                            </div>
                                        @endif
                                        @if (! MarketplaceHelper::hideStoreEmail() && $store->email)
                                            <div class="vendor-store-email mb-1">
                                                <i class="icon icon-envelope"></i>&nbsp;<a href="mailto:{{ $store->email }}">{{ $store->email }}</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="store-social-wrapper mt-4 mt-md-0 px-3">
                                @if (! MarketplaceHelper::hideStoreSocialLinks() && $socials = $store->getMetaData('socials', true))
                                    <ul class="store-social text-lg-end">
                                        @foreach ((array)$socials as $k => $link)
                                            <li>
                                                <a class="social-{{ $k }}" href="{{ $link }}" target="_blank">
                                                <span class="svg-icon">
                                                    <svg>
                                                        <use href="#svg-icon-{{ $k }}" xlink:href="#svg-icon-{{ $k }}"></use>
                                                    </svg>
                                                </span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <ul class="vendor-store-info mt-4 text-lg-end px-3">
                                <li class="vendor-store-register-date">
                                    <span>{{ __('Started from') }}: </span> {{ $store->created_at->translatedFormat('M d, Y') }}
                                </li>
                            </ul>
                        </div>
                        @if (!empty($showContactVendor))
                            <div class="col-12">
                                <div class="px-3">
                                    <a class="d-lg-none sidebar-filter-mobile text-white" href="#" data-toggle="contact-store-primary-sidebar">
                                        <span class="svg-icon me-2">
                                            <svg>
                                                <use href="#svg-icon-send" xlink:href="#svg-icon-send"></use>
                                            </svg>
                                        </span>
                                        <span>{{ __('Contact Vendor') }}</span>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @php
                $description = BaseHelper::clean($store->description);
                $content = BaseHelper::clean($store->content);
            @endphp

            @if ($description || $content)
                <div class="py-3 mb-3 bg-light">
                    <div class="px-3">
                        @if ($content)
                            <div id="store-content" style="display: none">
                                {!! $content !!}
                            </div>
                        @endif

                        <div id="store-short-description">
                            {!! $description ?: Str::limit($content, 250) !!}
                        </div>

                        <a href="#" class="text-link toggle-show-more">{{ __('show more') }}</a>
                        <a href="#" class="text-link toggle-show-less" style="display: none">{{ __('show less') }}</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
