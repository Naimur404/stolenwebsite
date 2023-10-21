@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')
@section('content')
    {!! Form::open(['route' => 'customer.post.change-password', 'method' => 'POST']) !!}
        <div class="form-header">
            <h3 class="h5">{{ SeoHelper::getTitle() }}</h3>
        </div>
        <div class="form-content">
            <div class="mb-3">
                <label for="old_password">{{ __('Current password') }}:</label>
                <input type="password" class="form-control @if ($errors->has('old_password')) is-invalid @endif" name="old_password" id="old_password"
                    placeholder="{{ __('Current Password') }}" required>
                @if ($errors->has('old_password'))
                    <div class="invalid-feedback">
                        {{ $errors->first('old_password') }}
                    </div>
                @endif
            </div>
            <div class="mb-3">
                <label for="password">{{ __('New password') }}:</label>
                <input type="password" class="form-control @if ($errors->has('password')) is-invalid @endif" name="password" id="password"
                    placeholder="{{ __('New Password') }}" required>
                @if ($errors->has('password'))
                    <div class="invalid-feedback">
                        {{ $errors->first('password') }}
                    </div>
                @endif
            </div>
            <div class="mb-3">
                <label for="password_confirmation">{{ __('Password confirmation') }}:</label>
                <input type="password" class="form-control @if ($errors->has('password_confirmation')) is-invalid @endif" name="password_confirmation" id="password_confirmation"
                    placeholder="{{ __('Password Confirmation') }}" required>
                @if ($errors->has('password_confirmation'))
                    <div class="invalid-feedback">
                        {{ $errors->first('password_confirmation') }}
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <button class="btn btn-primary">{{ __('Update') }}</button>
            </div>
        </div>
    {!! Form::close() !!}
@endsection
