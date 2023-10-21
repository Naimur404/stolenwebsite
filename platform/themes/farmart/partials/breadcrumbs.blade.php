<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        @foreach ($crumbs = Theme::breadcrumb()->getCrumbs() as $i => $crumb)
            @if ($i != (count($crumbs) - 1))
                <li class="breadcrumb-item">
                    <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                    <span class="extra-breadcrumb-name"></span>
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page">
                    <span>{{ $crumb['label'] }}</span>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
