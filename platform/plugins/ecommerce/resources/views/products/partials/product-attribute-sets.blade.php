<div class="row">
    @foreach ($productAttributeSets as $attributeSet)
        <div class="col-md-4 col-sm-6">
            <div class="form-group mb-3">
                <label for="attribute-{{ $attributeSet->slug }}" class="text-title-field required">{{ $attributeSet->title }}</label>
                @php
                    if ($selected = $productVariationsInfo ? $productVariationsInfo->firstWhere('attribute_set_id', $attributeSet->id) : null) {
                        $selected = [$selected->id => $selected->title];
                    } else {
                        $selected = ['' => '-- ' . trans('plugins/ecommerce::products.select') . ' --'];
                    }
                @endphp
                {!! Form::customSelect('attribute_sets[' . $attributeSet->id . ']',
                    $selected,
                    Arr::first(array_keys($selected)),
                    [
                        'id' => 'attribute-' . $attributeSet->slug,
                        'class' => 'select2-attributes select-search-full',
                        'data-id' => $attributeSet->id
                    ]
                ) !!}
            </div>
        </div>
    @endforeach
</div>
