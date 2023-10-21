<div class="form-group mb-3 option-field product-option-{{ Str::slug($option->name) }} product-option-{{ $option->id }}"
     style="margin-bottom: 10px">
    <div class="product-option-item-wrapper">
        <div class="product-option-item-label">
            <label class="{{ $option->required ? 'required' : '' }}">
                {{ $option->name }}
            </label>
        </div>
        <div class="product-option-item-values">
            <div class="form-radio">
                @foreach($option->values as $value)
                    <input type="hidden" name="options[{{ $option->id }}][option_type]" value="field" />
                    <input data-extra-price="0" {{ ($option->required) ? 'required' : '' }} type="text"
                        class="form-control" name="options[{{ $option->id }}][values]"
                        id="option-{{ $option->id }}-value-{{ Str::slug($option->values[0]['option_value']) }}">
                @endforeach
            </div>
        </div>
    </div>
</div>
