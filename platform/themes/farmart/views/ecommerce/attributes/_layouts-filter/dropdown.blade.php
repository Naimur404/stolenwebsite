<div @class([
        'dropdown-swatches-wrapper widget-filter-item',
        'd-none' => !empty($categoryId) && $set->categories->count() && ! $set->categories->contains('id', $categoryId),
    ]) data-type="dropdown" data-id="{{ $set->id }}"
    data-categories="{{ $set->categories->pluck('id')->toJson() }}">
    <h4 class="widget-title">{{ __('By :name', ['name' => $set->title]) }}</h4>
    <div class="widget-content">
        <div class="attribute-values">
            <div class="dropdown-swatch">
                <label>
                    <select class="form-control product-filter-item" name="attributes[]">
                        <option value="">{{ __('-- Select --') }}</option>
                        @foreach ($attributes->where('attribute_set_id', $set->id) as $attribute)
                            <option value="{{ $attribute->id }}" @selected(in_array($attribute->id, $selected))>{{ $attribute->title }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
        </div>
    </div>
</div>
