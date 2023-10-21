<div class="row {{ $sidebar == 'pre_footer_sidebar' ? 'row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-2 justify-content-center my-4 g-2' : 'row-cols-1 bg-light mb-5 py-3 px-4 g-0' }}">
    @for ($i = 1; $i <= 5; $i++)
        @if ($title = Arr::get(Arr::get($config['data'], $i), 'title'))
            <div class="col py-2">
                <div class="site-info__item d-flex align-items-start">
                    <div class="site-info__image me-3 mt-1">
                        <img class="lazyload" data-src="{{ RvMedia::getImageUrl(Arr::get(Arr::get($config['data'], $i), 'icon'), null, false, RvMedia::getDefaultImage()) }}" alt="{{ $title }}">
                    </div>
                    <div class="site-info__content">
                        <div class="site-info__title h4 fw-bold">{{ $title }}</div>
                        <div class="site-info__desc">{!! BaseHelper::clean(nl2br(Arr::get(Arr::get($config['data'], $i), 'subtitle'))) !!}</div>
                    </div>
                </div>
            </div>
        @endif
    @endfor
</div>
