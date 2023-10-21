@if (count($ads))
    <div class="widget-featured-banners py-5">
        <div class="container-xxxl">
            <div class="row row-cols-lg-3 row-cols-md-2 row-cols-1 justify-content-center">
                @for($i = 0; $i < count($ads); $i++)
                    <div class="col">
                        <div class="featured-banner-item img-fluid-eq my-2">
                            <div class="img-fluid-eq__dummy"></div>
                            <div class="img-fluid-eq__wrap">
                                {!! BaseHelper::clean($ads[$i]) !!}
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
@endif
