@php Theme::layout('full-width'); @endphp

{!! Theme::partial('page-header', ['withTitle' => false, 'size' => 'xl']) !!}
<div class="container">
    <div class="row customer-auth-page py-5 mt-5 justify-content-center">
        <div class="col-sm-9 col-md-6 col-lg-5 col-xl-4">
            <div class="customer-auth-form bg-light pt-1 py-3 px-4">
                <nav>
                    <div class="nav nav-tabs">
                        <h1 class="nav-link fs-5 fw-bold">{{ __('Log In Your Account') }}</h1>
                    </div>
                </nav>
                <div class="tab-content my-3">
                    <div class="tab-pane fade pt-4 show active" id="nav-login-content" role="tabpanel"
                        aria-labelledby="nav-home-tab">
                        @if (isset($errors) && $errors->has('confirmation'))
                            <div class="alert alert-danger">
                                <span>{!! BaseHelper::clean($errors->first('confirmation')) !!}</span>
                            </div>
                            <br>
                        @endif
                        <form class="mt-3" method="POST" action="{{ route('customer.login.post') }}">
                            @csrf
                            <div class="mb-3">
                                <input class="form-control @if ($errors->has('email')) is-invalid @endif" type="text" required=""
                                    placeholder="{{ __('Your Email') }}" name="email"
                                    autocomplete="email" value="{{ old('email') }}">
                                @if ($errors->has('email'))
                                    <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                            <div class="input-group mb-3 input-group-with-text">
                                <input class="form-control @if ($errors->has('password')) is-invalid @endif" type="password" placeholder="{{ __('Password') }}"
                                    aria-label="{{ __('Password') }}" autocomplete="current-password" name="password">
                                <span class="input-group-text">
                                    <a class="lost-password" href="{{ route('customer.password.reset') }}">{{ __('Forgot?') }}</a>
                                </span>
                                @if ($errors->has('password'))
                                    <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" name="remember" id="remember-me"
                                    type="checkbox" value="1" @if (old('is_vendor') == 1) checked="checked" @endif>
                                <label class="form-check-label" for="remember-me">{{ __('Remember me?') }}</label>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary" type="submit">{{ __('Log in') }}</button>
                            </div>
                            <div class="mt-3">
                                <p class="text-center">{{ __("Don't Have an Account?") }} <a href="{{ route('customer.register') }}" class="d-inline-block text-primary">{{ __('Sign up now') }}</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="bg-light pt-1 px-4 pb-3">
                {!! apply_filters(BASE_FILTER_AFTER_LOGIN_OR_REGISTER_FORM, null, \Botble\Ecommerce\Models\Customer::class) !!}
            </div>
        </div>
    </div>
</div>
