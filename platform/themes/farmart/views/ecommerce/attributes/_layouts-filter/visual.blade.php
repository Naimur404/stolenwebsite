<div @class([
        'visual-swatches-wrapper widget--colors widget-filter-item',
        'd-none' => !empty($categoryId) && $set->categories->count() && ! $set->categories->contains('id', $categoryId),
    ]) data-id="{{ $set->id }}" data-type="visual"
    data-categories="{{ $set->categories->pluck('id')->toJson() }}">
    <h4 class="widget-title">{{ __('By :name', ['name' => $set->title]) }}</h4>
    <div class="widget-content">
        <div class="attribute-values">
            <ul class="visual-swatch color-swatch">
                @foreach($attributes->where('attribute_set_id', $set->id) as $attribute)
                    <li data-slug="{{ $attribute->slug }}"
                        title="{{ $attribute->title }}">
                        <div class="custom-checkbox">
                            <label>
                                <input class="form-control product-filter-item" type="checkbox" name="attributes[]" value="{{ $attribute->id }}" @checked(in_array($attribute->id, $selected))>
				                <span style="{{ $attribute->getAttributeStyle() }}"></span>
                            </label>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
