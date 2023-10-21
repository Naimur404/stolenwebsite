@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')
@section('content')
    <div class="alert alert-primary" role="alert">
        <h3 class="alert-heading">{{ SeoHelper::getTitle() }}</h3>
        <hr>
        <p>{{ __('Please wait for the administrator to review and approve!') }}</p>
    </div>
@endsection

