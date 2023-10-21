@php
    $value = isset($value) ? (array) $value : [];
@endphp
@if ($categories)
    <ul>
        @foreach ($categories as $category)
            @if ($category->id != $currentId)
                <li value="{{ $category->id ?? '' }}" @selected($category->id)>
                    {!! Form::customCheckbox([
                        [$name, $category->id, $category->name, in_array($category->id, $value)]
                    ]) !!}
                    @include('plugins/ecommerce::product-categories.partials.categories-checkbox-option-line', [
                        'categories' => $category->activeChildren,
                        'value' => $value,
                        'currentId' => $currentId,
                        'name' => $name
                    ])
                </li>
            @endif
        @endforeach
    </ul>
@endif
