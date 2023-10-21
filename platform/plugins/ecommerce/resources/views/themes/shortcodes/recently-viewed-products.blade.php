<section>
    <h3>{{ $shortcode->title }}</h3>
    <div class="row">
        @foreach($products as $product)
            <div class="col-lg-3 col-md-4 col-12 col-sm-6">
                @include($productItemView, compact('product'))
            </div>
        @endforeach
    </div>
</section>
