<div class="container">
    <div class="row justify-content-center">
        <div class="col">
            <div class="row faqs-nav-tab">
                <div class="col-md-3">
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        @foreach($categories as $category)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if ($loop->first) active @endif" id="faq-tab-{{ $loop->index }}" data-bs-toggle="tab"
                                    data-bs-target="#faq-content-{{ $loop->index }}" type="button" role="tab"
                                    aria-controls="faq-content-{{ $loop->index }}" aria-selected="true">{{ $category->name }}</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="faq-tab-content">
                        @foreach($categories as $category)
                            <div class="tab-pane fade @if ($loop->first) show active @endif" role="tabpanel"
                                aria-labelledby="home-tab" id="faq-content-{{ $loop->index }}">
                                <div class="row row-cols-sm-2 row-cols-1">
                                    @foreach($category->faqs->chunk(round($category->count() / 2)) as $faqs)
                                        <div class="col">
                                            @foreach($faqs as $faq)
                                            <div class="faq-tab-wrapper mb-4 pb-4">
                                                <h4 class="faq-title">{{ $faq->question }}</h4>
                                                <div class="faq-desc">{!! BaseHelper::clean($faq->answer) !!}</div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
