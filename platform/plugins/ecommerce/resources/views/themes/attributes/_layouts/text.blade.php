<div class="text-swatches-wrapper attribute-swatches-wrapper"
    data-type="text" data-slug="{{ $set->slug }}">
    <div class="attribute-name">{{ $set->title }}</div>
    <div class="attribute-values">
        <ul class="text-swatch attribute-swatch">
            @foreach($attributes->where('attribute_set_id', $set->id) as $attribute)
                <li data-slug="{{ $attribute->slug }}"
                    data-id="{{ $attribute->id }}"
                    @class([
                        'attribute-swatch-item',
                        'pe-none' => ! $variationInfo->where('id', $attribute->id)->count(),
                    ])>
                    <div class="custom-radio">
                        <label>
                            <input class="product-filter-item"
                                type="radio"
                                name="attribute_{{ $set->slug }}_{{ $key }}"
                                value="{{ $attribute->id }}"
                                data-slug="{{ $attribute->slug }}"
                                @checked($selected->where('id', $attribute->id)->count())>
                            <span>{{ $attribute->title }}</span>
                        </label>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
