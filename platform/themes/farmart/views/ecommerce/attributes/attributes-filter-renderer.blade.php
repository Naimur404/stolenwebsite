@php
    $selected = (array)request()->query('attributes', []);
@endphp
@foreach ($attributeSets as $attributeSet)
    @if (view()->exists(Theme::getThemeNamespace('views.ecommerce.attributes._layouts-filter.' . $attributeSet->display_layout)))
        @include(Theme::getThemeNamespace('views.ecommerce.attributes._layouts-filter.' . $attributeSet->display_layout), [
            'set' => $attributeSet,
            'attributes' => $attributeSet->attributes,
        ])
    @else
        @include(Theme::getThemeNamespace('views.ecommerce.attributes._layouts-filter.dropdown'), [
            'set' => $attributeSet,
            'attributes' => $attributeSet->attributes,
        ])
    @endif
@endforeach
