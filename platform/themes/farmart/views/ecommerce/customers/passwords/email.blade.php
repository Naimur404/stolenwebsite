@php Theme::layout('full-width'); @endphp

{!! Theme::partial('page-header', ['size' => 'xl', 'withTitle' => true]) !!}

<div class="container">
    <div class="row forgot-password-page py-5 mt-3 justify-content-center">
        <div class="col-sm-6">
            <div class="forgot-password-form bg-light p-4">
                <p>{{ __('Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.') }}</p>
                <form class="mt-3"  method="POST" action="{{ route('customer.password.request') }}">
                    @csrf
                    <div class="mb-3">
                        <input class="form-control @if ($errors->has('email')) is-invalid @endif" type="text" required="" placeholder="{{ __('Email address') }}"
                            name="email" autocomplete="email" value="{{ old('email') }}">
                        @if ($errors->has('email'))
                            <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">{{ __('Send Password Reset Link') }}</button>
                    </div>
                </form>

                @if (session('status'))
                    <div class="text-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('success_msg'))
                    <div class="text-success">
                        {{ session('success_msg') }}
                    </div>
                @endif

                @if (session('error_msg'))
                    <div class="text-danger">
                        {{ session('error_msg') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
