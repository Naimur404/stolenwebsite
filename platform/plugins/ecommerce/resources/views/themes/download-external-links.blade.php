<section class="section--blog">
    <div class="section__content">
        <section class="section--auth">
            <div class="form__header">
                <h3>{{ __('Download product ":name" with external links', ['name' => $orderProduct->product_name]) }}</h3>
                <p>{{ __('You can now download it by clicking the links below') }}</p>
            </div>
            <ol class="list-group list-group-numbered list-group-flush">
                @foreach ($externalProductFiles as $productFile)
                    <li class="list-group-item">
                        <a target="_blank" href="{{ $productFile->url }}">{{ $productFile->file_name ?: $productFile->url }}</a>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</section>
