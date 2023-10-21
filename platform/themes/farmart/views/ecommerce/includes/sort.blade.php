@if ($sortParams = EcommerceHelper::getSortParams())
    @php
        $sortBy = request()->input('sort-by');
        if ($sortBy && Arr::has($sortParams, $sortBy)) {
            $sortByLabel = Arr::get($sortParams, $sortBy);
        } else {
            $sortBy = array_key_first($sortParams);
            $sortByLabel = Arr::first($sortParams);
        }
    @endphp
    <div class="col-auto">
        <div class="catalog-toolbar__ordering d-flex align-items-center me-md-4">
            <input type="hidden" name="sort-by" value="{{ $sortBy }}">
            <div class="text d-none d-lg-block">{{ __('Sort by') }}</div>
            <div class="dropdown">
                <a class="btn btn-secondary dropdown-toggle" id="dropdown-toolbar__ordering" href="#"
                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span>{{ $sortByLabel }}</span>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdown-toolbar__ordering">
                    @foreach ($sortParams as $key => $name)
                        <li @class(['active' => $sortBy == $key])>
                            <a class="dropdown-item" href="#" data-value="{{ $key }}">{{ $name }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
