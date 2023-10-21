<div class="visual-swatches-wrapper attribute-swatches-wrapper"
    data-type="visual" data-slug="{{ $set->slug }}">
    <label class="attribute-name">{{ $set->title }}</label>
    <div class="attribute-values">
        <ul class="visual-swatch attribute-swatch">
            @foreach($attributes->where('attribute_set_id', $set->id) as $attribute)
                <li data-slug="{{ $attribute->slug }}"
                    data-id="{{ $attribute->id }}"
                    data-bs-toggle="tooltip"
                    data-placement="top"
                    title="{{ $attribute->title }}"
                    @class([
                        'attribute-swatch-item',
                        'pe-none' => !$variationInfo->where('id', $attribute->id)->count(),
                    ])>
                    <div class="custom-radio">
                        <label>
                            <input class="form-control product-filter-item"
                                type="radio"
                                name="attribute_{{ $set->slug }}_{{ $key }}"
                                value="{{ $attribute->id }}"
                                data-slug="{{ $attribute->slug }}"
                                @checked($selected->where('id', $attribute->id)->count())>
                            <span style="{{ $attribute->getAttributeStyle() }}"></span>
                        </label>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
