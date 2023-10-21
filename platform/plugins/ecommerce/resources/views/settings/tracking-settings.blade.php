@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="max-width-1200">
        {!! Form::open(['url' => route('ecommerce.tracking-settings'), 'class' => 'main-setting-form']) !!}
            <x-core-setting::section
                :title="trans('plugins/ecommerce::ecommerce.setting.tracking_settings')"
                :description="trans('plugins/ecommerce::ecommerce.setting.tracking_settings_description')"
            >
                <x-core-setting::on-off
                    name="facebook_pixel_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_facebook_pixel')"
                    :value="EcommerceHelper::isFacebookPixelEnabled()"
                    class="trigger-input-option"
                    data-setting-container=".facebook-pixel-settings-container"
                    :helper-text="trans('plugins/ecommerce::ecommerce.setting.facebook_pixel_helper')"
                />

                <div @class(['facebook-pixel-settings-container mb-4 border rounded-top rounded-bottom p-3 bg-light', 'd-none' => ! EcommerceHelper::isFacebookPixelEnabled()])>
                    <x-core-setting::text-input
                        name="facebook_pixel_id"
                        :label="trans('plugins/ecommerce::ecommerce.setting.facebook_pixel_id')"
                        :value="get_ecommerce_setting('facebook_pixel_id')"
                    />
                </div>

                <x-core-setting::on-off
                    name="google_tag_manager_enabled"
                    :label="trans('plugins/ecommerce::ecommerce.setting.enable_google_tag_manager')"
                    :value="EcommerceHelper::isGoogleTagManagerEnabled()"
                    class="trigger-input-option"
                    data-setting-container=".google-tag-manager-settings-container"
                    :helper-text="trans('plugins/ecommerce::ecommerce.setting.google_tag_manager_helper')"
                />

                <div @class(['google-tag-manager-settings-container mb-4 border rounded-top rounded-bottom p-3 bg-light', 'd-none' => ! EcommerceHelper::isGoogleTagManagerEnabled()])>
                    <div class="form-group mb-3">
                        <label class="text-title-field mb-2" for="google_tag_manager_code">{{ trans('plugins/ecommerce::ecommerce.setting.google_tag_manager_code') }}</label>
                        <textarea rows="3" name="google_tag_manager_code" id="google_tag_manager_code" class="next-input">{{ old('google_tag_manager_code', get_ecommerce_setting('google_tag_manager_code')) }}</textarea>
                    </div>
                </div>
            </x-core-setting::section>

            <div class="flexbox-annotated-section" style="border: none">
                <div class="flexbox-annotated-section-annotation">&nbsp;</div>
                <div class="flexbox-annotated-section-content">
                    <button class="btn btn-info" type="submit">{{ trans('plugins/ecommerce::currency.save_settings') }}</button>
                </div>
            </div>
        {!! Form::close() !!}
    </div>
    {!! $jsValidation !!}
@endsection

@push('footer')
    <script>
        $(document).ready(() => {
            'use strict';
            Botble.initCodeEditor('google_tag_manager_code', 'javascript');
        });
    </script>

@endpush
