<div class="image-box">
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" class="image-data">
    <input type="file" name="{{ $name }}_input" class="image_input" accept="image/*" style="display: none;">
    <div class="preview-image-wrapper">
        <img src="{{ RvMedia::getImageUrl($value, 'thumb', false, RvMedia::getDefaultImage()) }}" data-default-image="{{ RvMedia::getDefaultImage() }}" alt="{{ trans('core/base::base.preview_image') }}" class="preview_image" width="150">
        <a class="btn_remove_image" title="{{ trans('core/base::forms.remove_image') }}">
            <i class="fa fa-times"></i>
        </a>
    </div>
    <div class="image-box-actions">
        <a href="#" class="custom-select-image">
            {{ trans('core/base::forms.choose_image') }}
        </a>
    </div>
</div>
