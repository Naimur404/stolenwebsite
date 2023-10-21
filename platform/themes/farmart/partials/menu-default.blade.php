<ul {!! $options !!}>
    @foreach ($menu_nodes->loadMissing('metadata') as $key => $row)
        <li @if ($row->css_class || $row->active) class="@if ($row->css_class) {{ $row->css_class }} @endif @if ($row->active) current @endif" @endif>
            <a href="{{ url($row->url) }}" @if ($row->target !== '_self') target="{{ $row->target }}" @endif>
                @if ($iconImage = $row->getMetadata('icon_image', true))
                    <img src="{{ RvMedia::getImageUrl($iconImage) }}" alt="icon image" width="14" height="14"/>
                @elseif ($row->icon_font) <i class="{{ trim($row->icon_font) }}"></i> @endif <span>{{ $row->title }}</span>
            </a>
            @if ($row->has_child)
                {!! Menu::generateMenu([
                    'menu'       => $menu,
                    'menu_nodes' => $row->child
                ]) !!}
            @endif
        </li>
    @endforeach
</ul>
