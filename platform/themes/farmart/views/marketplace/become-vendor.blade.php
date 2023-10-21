@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')
@section('content')
    {!! Form::open(['route' => 'marketplace.vendor.become-vendor', 'method' => 'POST']) !!}
        <div class="form__header">
            <h3>{{ SeoHelper::getTitle() }}</h3>
        </div>

        <div class="form__content">
            <input type="hidden" name="is_vendor" value="1">
            @include(Theme::getThemeNamespace() . '::views.marketplace.includes.become-vendor-form', ['isRegister' => true])

            <div class="form-group text-center">
                <button class="btn btn-primary">{{ __('Register') }}</button>
            </div>
        </div>
    {!! Form::close() !!}
@endsection
