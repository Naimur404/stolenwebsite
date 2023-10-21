<div @class([
        'text-swatches-wrapper widget-filter-item',
        'd-none' => !empty($categoryId) && $set->categories->count() && ! $set->categories->contains('id', $categoryId),
    ])
    data-type="text" data-id="{{ $set->id }}"
    data-categories="{{ $set->categories->pluck('id')->toJson() }}">
    <h4 class="widget-title">{{ __('By :name', ['name' => $set->title]) }}</h4>
    <div class="widget-content">
        <div class="attribute-values">
            <ul class="text-swatch">
                @foreach ($attributes->where('attribute_set_id', $set->id) as $attribute)
                    <li data-slug="{{ $attribute->slug }}">
                        <div>
                            <label>
                                <input class="product-filter-item" type="checkbox" name="attributes[]" value="{{ $attribute->id }}" @checked(in_array($attribute->id, $selected))>
                                <span>{{ $attribute->title }}</span>
                            </label>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
