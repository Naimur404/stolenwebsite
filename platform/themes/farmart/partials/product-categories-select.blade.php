@foreach ($categories as $category)
    <option value="{{ $category['id'] }}">{!! $indent !!}{{ $category['name'] }}</option>
    @if (isset($category['active_children'][0]))
        {!! Theme::partial('product-categories-select', ['categories' => $category['active_children'], 'indent' => $indent . '&nbsp;&nbsp;']) !!}
    @endif
@endforeach
