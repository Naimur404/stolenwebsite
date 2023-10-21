@php Theme::layout('full-width'); @endphp

{!! Theme::partial('page-header', ['withTitle' => false, 'size' => 'xl']) !!}
<div class="container">
    <div class="row customer-auth-page py-5 mt-5 justify-content-center">
        <div class="col-sm-9 col-md-6 col-lg-5 col-xl-4">
            <div class="customer-auth-form bg-light pt-1 py-3 px-4">
                <nav>
                    <div class="nav nav-tabs">
                        <h1 class="nav-link fs-5 fw-bold">{{ __('Register An Account') }}</h1>
                    </div>
                </nav>
                <div class="tab-content my-3">
                    <div class="tab-pane fade pt-4 show active" id="nav-register-content" role="tabpanel"
                         aria-labelledby="nav-profile-tab">
                        <form method="POST" action="{{ route('customer.register.post') }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label required" for="name-register">{{ __('Name') }}</label>
                                <input class="form-control @if ($errors->has('name')) is-invalid @endif"
                                       name="name" id="name-register" type="text" value="{{ old('name') }}" placeholder="{{ __('Your Name') }}">
                                @if ($errors->has('name'))
                                    <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label required" for="email-register">{{ __('Email Address') }}</label>
                                <input class="form-control @if ($errors->has('email')) is-invalid @endif"
                                       type="email" required="required" placeholder="{{ __('Email Address') }}"
                                       name="email" autocomplete="email" id="email-register" value="{{ old('email') }}">
                                @if ($errors->has('email'))
                                    <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label required" for="password-register">{{ __('Password') }}</label>
                                <input class="form-control @if ($errors->has('password')) is-invalid @endif"
                                       type="password" placeholder="{{ __('Password') }}" aria-label="{{ __('Password') }}"
                                       autocomplete="new-password" id="password-register" name="password">
                                @if ($errors->has('password'))
                                    <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label required" for="password-confirmation-register">{{ __('Password confirmation') }}</label>
                                <input class="form-control @if ($errors->has('password_confirmation')) is-invalid @endif"
                                       type="password" placeholder="{{ __('Password confirmation') }}" id="password-confirmation-register" aria-label="{{ __('Password confirmation') }}" name="password_confirmation">
                                @if ($errors->has('password_confirmation'))
                                    <div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
                                @endif
                            </div>
                            @if (is_plugin_active('marketplace'))
                                <div class="show-if-vendor" @if (old('is_vendor') != 1) style="display: none" @endif>
                                    @include(Theme::getThemeNamespace() . '::views.marketplace.includes.become-vendor-form')
                                </div>
                                <div class="vendor-customer-registration">
                                    <div class="form-check my-1">
                                        <input class="form-check-input" name="is_vendor" value="0" id="customer-role-register"
                                               type="radio" @if (old('is_vendor') != 1) checked="checked" @endif>
                                        <label class="form-check-label" for="customer-role-register">{{ __('I am a customer') }}</label>
                                    </div>
                                    <div class="form-check my-1 mb-3">
                                        <input class="form-check-input" id="vendor-role-register" value="1"
                                               type="radio" name="is_vendor" @if (old('is_vendor') == 1) checked="checked" @endif>
                                        <label class="form-check-label" for="vendor-role-register">{{ __('I am a vendor') }}</label>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group">
                                <p>{{ __('Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our privacy policy.') }}</p>
                            </div>

                            @if (is_plugin_active('captcha'))
                                @if(Captcha::isEnabled() && get_ecommerce_setting('enable_recaptcha_in_register_page', 0))
                                    <div class="form-group mb-3">
                                        {!! Captcha::display() !!}
                                    </div>
                                @endif

                                @if (get_ecommerce_setting('enable_math_captcha_in_register_page', 0))
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="math-group">{{ app('math-captcha')->label() }}</label>
                                        {!! app('math-captcha')->input(['class' => 'form-control', 'id' => 'math-group', 'placeholder' => app('math-captcha')->getMathLabelOnly()]) !!}
                                    </div>
                                @endif
                            @endif

                            <div class="form-check mb-3">
                                <input type="hidden" name="agree_terms_and_policy" value="0">
                                <input class="form-check-input" type="checkbox" name="agree_terms_and_policy" id="agree-terms-and-policy" value="1" @if (old('agree_terms_and_policy') == 1) checked @endif>
                                <label for="agree-terms-and-policy">{!! BaseHelper::clean(__('I agree to terms & Policy.')) !!}</label>
                                @if ($errors->has('agree_terms_and_policy'))
                                    <div class="mt-1">
                                        <span class="text-danger small">{{ $errors->first('agree_terms_and_policy') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-primary" type="submit">{{ __('Register') }}</button>
                            </div>

                            <div class="mt-3">
                                <p class="text-center">{{ __('Already have an account?') }} <a href="{{ route('customer.login') }}" class="d-inline-block text-primary">{{ __('Log in') }}</a></p>
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
