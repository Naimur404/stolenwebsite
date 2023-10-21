@if (is_plugin_active('ads'))
    @if ($image = display_ads_advanced($config['ads_key'], ['class' => 'd-flex justify-content-center']))
        <div class="lazyload" @if ($config['background']) data-bg="{{ RvMedia::getImageUrl($config['background']) }}" @endif>
            @php
                $size = 'xxxl';
                switch ($config['size']) {
                    case 'large':
                        $size = 'xxl';
                        break;
                    case 'medium':
                        $size = 'lg';
                        break;
                }
            @endphp
            <div class="container-{{ $size }}">
                <div class="row">
                    <div class="my-5">
                        {!! BaseHelper::clean($image) !!}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
