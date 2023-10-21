@foreach ($productAttributeSets as $attributeSet)
    <label>
        <input type="checkbox" class="attribute-set-item" name="attribute_sets[]" value="{{ $attributeSet->id }}" @checked($attributeSet->is_selected)>
        {{ $attributeSet->title }}
    </label> &nbsp;
@endforeach

<div class="alert alert-warning mt-3">
    <span>{{ trans('plugins/ecommerce::products.this_action_will_reload_page') }}</span>
</div>
