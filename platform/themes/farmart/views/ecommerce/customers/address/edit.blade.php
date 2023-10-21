@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')

@section('content')
    @include(Theme::getThemeNamespace() . '::views.ecommerce.customers.address.form', [
        'url' => route('customer.address.edit', $address->id),
    ])
@endsection
