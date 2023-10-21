@php
    $categories->loadMissing([
        'slugable',
        'activeChildren:id,name,parent_id',
        'activeChildren.slugable',
    ]);

    $categoriesRequest ??= [];
    $activeCategoryId ??= 0;
@endphp

<ul @class(['loading-skeleton' => $categoriesRequest])>
    @foreach ($categories as $category)
        @if (! empty($categoriesRequest) && $loop->first && ! $category->parent_id)
            <li class="category-filter show-all-product-categories mb-2">
                <a class="nav-list__item-link" href="{{ route('public.products') }}" data-id="">
                <span class="cat-menu-close svg-icon">
                    <svg>
                        <use href="#svg-icon-chevron-left" xlink:href="#svg-icon-close"></use>
                    </svg>
                </span>
                    <span>{{ __('All categories') }}</span>
                </a>
            </li>
        @endif
        <li @class([
                'category-filter',
                'opened' => in_array($category->id, $categoriesRequest) && ($activeCategoryId == $category->id || $urlCurrent != $category->url),
            ])>
            <div class="widget-layered-nav-list__item">
                <div class="nav-list__item-title">
                    <a @class([
                            'nav-list__item-link',
                            'active' => $activeCategoryId == $category->id || $urlCurrent == $category->url,
                        ]) href="{{ $category->url }}" data-id="{{ $category->id }}">
                        @if (! $category->parent_id)
                            @if ($categoryImage = $category->getMetaData('icon_image', true))
                                <img src="{{ RvMedia::getImageUrl($categoryImage) }}"
                                    alt="{{ $category->name }}" width="18" height="18">
                            @elseif ($categoryIcon = $category->getMetaData('icon', true))
                                <i class="{{ $categoryIcon }}"></i>
                            @endif
                            <span class="ms-1">{{ $category->name }}</span>
                        @else
                            <span>{{ $category->name }}</span>
                        @endif
                    </a>
                </div>
                @if ($category->activeChildren->count())
                    <span class="cat-menu-close svg-icon closed-icon">
                        <svg>
                            <use href="#svg-icon-increase" xlink:href="#svg-icon-increase"></use>
                        </svg>
                    </span>

                    <span class="cat-menu-close svg-icon opened-icon">
                        <svg>
                            <use href="#svg-icon-decrease" xlink:href="#svg-icon-decrease"></use>
                        </svg>
                    </span>
                @endif
            </div>
            @if ($category->activeChildren->count())
                @include(Theme::getThemeNamespace('views.ecommerce.includes.categories'), [
                    'categories' => $category->activeChildren,
                ])
            @endif
        </li>
    @endforeach
</ul>
