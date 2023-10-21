{!! Theme::partial('page-header', ['withTitle' => true]) !!}

<div class="container">
    <div class="row">
        <div class="col-md-9 blog-page-content">
            <div class="mb-4 blog-page-content-wrapper">
                @foreach ($posts as $post)
                    {!! Theme::partial('post-item', compact('post')) !!}
                @endforeach
            </div>
            {!! $posts->withQueryString()->links() !!}
        </div>
        <div class="col-md-3">
            <div class="primary-sidebar">
                <aside class="widget-area" id="primary-sidebar">
                    {!! dynamic_sidebar('primary_sidebar') !!}
                </aside>
            </div>
        </div>
    </div>
</div>
