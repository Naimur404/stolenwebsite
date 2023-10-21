@if (is_plugin_active('ecommerce'))
    <div>
        <p>
            <strong>{{ $config['name'] }}:</strong>
            @foreach($categories as $category)
                <a href="{{ $category->url }}" title="{{ $category->name }}">{{ $category->name }}</a>
            @endforeach
        </p>
    </div>
@endif
