<div class="ms-md-5 ps-md-5">
    <h2>{{ __('Drop Us A Line') }}</h2>
    <form class="mt-5 contact-form" action="{{ route('public.send.contact') }}" method="POST" role="form">
        @csrf

        {!! apply_filters('pre_contact_form', null) !!}

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="d-none sr-only" for="contact-name">{{ __('Name') }}</label>
                    <input class="form-control py-3 px-3" type="text" id="contact-name" name="name" value="{{ old('name') }}" placeholder="{{ __('Your Name *') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="d-none sr-only" for="contact-email">{{ __('Email') }}</label>
                    <input class="form-control py-3 px-3" type="email" id="contact-email" name="email"  value="{{ old('email') }}" placeholder="{{ __('Your Email *') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="d-none sr-only" for="contact-phone">{{ __('Phone') }}</label>
                    <input class="form-control py-3 px-3" type="text" id="contact-phone" name="phone"  value="{{ old('phone') }}" placeholder="{{ __('Your Phone') }}">
                </div>
            </div>
            <div class="col-12">
                <div class="mb-3">
                    <label class="d-none sr-only" for="contact-subject">{{ __('Subject') }}</label>
                    <input class="form-control py-3 px-3" type="text" id="contact-subject" name="subject" value="{{ old('subject') }}" placeholder="{{ __('Subject (optional)') }}">
                </div>
            </div>
            <div class="col-12">
                <div class="mb-3">
                    <label class="d-none sr-only" for="contact-message">{{ __('Message') }}</label>
                    <textarea class="form-control py-3 px-3" id="contact-message" name="content" cols="40" rows="10" placeholder="{{ __('Write your message here') }}">{{ old('content') }}</textarea>
                </div>
            </div>

            @if (is_plugin_active('captcha'))
                @if (setting('enable_captcha'))
                    <div class="col-12">
                        <div class="mb-3">
                            {!! Captcha::display() !!}
                        </div>
                    </div>
                @endif

                @if (setting('enable_math_captcha_for_contact_form', 0))
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="math-group">{{ app('math-captcha')->label() }}</label>
                            {!! app('math-captcha')->input(['class' => 'form-control', 'id' => 'math-group', 'placeholder' => app('math-captcha')->getMathLabelOnly() . ' = ?']) !!}
                        </div>
                    </div>
                @endif
            @endif

            {!! apply_filters('after_contact_form', null) !!}

            <div class="col-12">
                <div class="mt-4">
                    <button class="btn btn-primary" type="submit">{{ __('Send Message') }}</button>
                </div>
            </div>
            <div class="col-12">
                <div class="contact-form-group mt-4">
                    <div class="contact-message contact-success-message" style="display: none"></div>
                    <div class="contact-message contact-error-message" style="display: none"></div>
                </div>
            </div>
        </div>
    </form>
</div>
