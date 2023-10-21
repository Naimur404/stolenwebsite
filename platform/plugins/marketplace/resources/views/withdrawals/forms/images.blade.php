<div id="withdrawal-images-container">
    <div class="row">
        @foreach ($model->images as $image)
            <div class="col-md-4 col-6">
                <a class="fancybox" href="{{ RvMedia::getImageUrl($image) }}">
                    <img src="{{ RvMedia::getImageUrl($image, 'thumb') }}" alt="{{ $model->id }}"/>
                </a>
            </div>
        @endforeach
    </div>
</div>
