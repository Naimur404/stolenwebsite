<x-core-setting::section
    :title="trans('plugins/simple-slider::simple-slider.settings.title')"
    :description="trans('plugins/simple-slider::simple-slider.settings.description')"
>
    <div class="form-group mb-3">
        <label class="text-title-field"
               for="simple_slider_using_assets">{{ trans('plugins/simple-slider::simple-slider.settings.using_assets') }}
        </label>
        <label class="me-2">
            <input type="radio" name="simple_slider_using_assets"
                   value="1"
                   @if (setting('simple_slider_using_assets', true)) checked @endif>{{ trans('core/setting::setting.general.yes') }}
        </label>
        <label>
            <input type="radio" name="simple_slider_using_assets"
                   value="0"
                   @if (!setting('simple_slider_using_assets', true)) checked @endif>{{ trans('core/setting::setting.general.no') }}
        </label>
    </div>
    <div class="form-group mb-3">
        <p>{{ trans('plugins/simple-slider::simple-slider.settings.using_assets_description') }}</p>
        <pre><strong>
/vendor/core/plugins/simple-slider/libraries/owl-carousel/owl.carousel.css
/vendor/core/plugins/simple-slider/css/simple-slider.css
/vendor/core/plugins/simple-slider/libraries/owl-carousel/owl.carousel.js
/vendor/core/plugins/simple-slider/js/simple-slider.js
        </strong></pre>
    </div>
</x-core-setting::section>
