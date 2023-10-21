<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ __('Image') }}</th>
                <th>{{ __('Product Name') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Star') }}</th>
                <th width="200">{{ __('Comment') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
        @if ($reviews->total() > 0)
            @foreach ($reviews as $item)
                <tr>
                    <th scope="row">
                        <img src="{{ RvMedia::getImageUrl($item->product->image, 'thumb', false, RvMedia::getDefaultImage())}}"
                            alt="{{ $item->product->name }}" class="img-thumb" style="max-width: 70px">
                    </th>
                    <th scope="row">
                        <a href="{{ $item->product->url }}">{{ $item->product->name }}</a>
                    </th>
                    <td>{{ $item->created_at->translatedFormat('M d, Y h:m') }}</td>
                    <td>
                        <span>{{ $item->star }}</span>
                        <span class="ecommerce-icon text-primary">
                            <svg>
                                <use href="#ecommerce-icon-star-o" xlink:href="#ecommerce-icon-star-o"></use>
                            </svg>
                        </span>
                    </td>
                    <td>{{ Str::limit($item->comment, 120) }}</td>
                    <td>
                        {!! Form::open([
                            'url' => route('public.reviews.destroy', $item->id),
                            'onSubmit' => 'return confirm("' . __('Do you really want to delete the review?') . '")']) !!}
                            <input type="hidden" name="_method" value="DELETE">
                            <button class="btn btn-danger btn-sm">{{ __('Delete') }}</button>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="6" class="text-center">{{ __('No reviews!') }}</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>

<div class="pagination">
    {!! $reviews->links() !!}
</div>
