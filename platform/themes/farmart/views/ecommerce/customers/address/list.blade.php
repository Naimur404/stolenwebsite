@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')

@section('content')
    <p>{{ __('The following addresses will be used on the checkout page by default.') }}</p>
    <div class="customer-address py-3">
        <div class="d-flex justify-content-between py-2">
            <h4>{{ SeoHelper::getTitle() }}</h4>
            <a class="text-primary" href="{{ route('customer.address.create') }}">{{ __('Add') }}</a>
        </div>
        @include(Theme::getThemeNamespace() . '::views.ecommerce.customers.address.items')
        <div class="row">
            <div class="col-12">
                {!! $addresses->links() !!}
            </div>
        </div>
    </div>
@endsection
