@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')
@section('content')
    {!! Form::open(['route' => 'customer.edit-account', 'method' => 'POST']) !!}
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a href="#tab_profile" class="nav-link active" data-toggle="tab">{{ SeoHelper::getTitle() }} </a>
            </li>
            {!! apply_filters(BASE_FILTER_REGISTER_CONTENT_TABS, null, auth('customer')->user()) !!}
        </ul>
        <div class="tab-content px-2 py-4 border border-top-0">
            <div class="tab-pane active" id="tab_profile">
                <div class="form-content">
                    <div class="mb-3">
                        <label for="name">{{ __('Full Name') }}:</label>
                        <input id="name" type="text" class="form-control @if ($errors->has('name')) is-invalid @endif" name="name" value="{{ auth('customer')->user()->name }}">
                        {!! Form::error('name', $errors) !!}
                    </div>
        
                    <div class="mb-3">
                        <label for="date_of_birth">{{ __('Date of birth') }}:</label>
                        <input id="date_of_birth" type="text" class="form-control @if ($errors->has('dob')) is-invalid @endif" name="dob" value="{{ auth('customer')->user()->dob }}">
                        {!! Form::error('dob', $errors) !!}
                    </div>

                    <div class="mb-3">
                        <label for="email">{{ __('Email') }}:</label>
                        <input id="email" type="email" class="form-control" disabled="disabled" value="{{ auth('customer')->user()->email }}" name="email">
                    </div>
        
                    <div class="mb-3">
                        <label for="phone">{{ __('Phone') }}</label>
                        <input type="text" class="form-control @if ($errors->has('phone')) is-invalid @endif" name="phone" id="phone" placeholder="{{ __('Phone') }}" value="{{ auth('customer')->user()->phone }}">
                        {!! Form::error('phone', $errors) !!}
                    </div>
                </div>
            </div>
            {!! apply_filters(BASE_FILTER_REGISTER_CONTENT_TAB_INSIDE, null, auth('customer')->user()) !!}
        </div>
        <div class="my-3">
            <button class="btn btn-primary">{{ __('Update') }}</button>
        </div>
    {!! Form::close() !!}
@endsection
