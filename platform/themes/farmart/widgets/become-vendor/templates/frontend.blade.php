@if (is_plugin_active('marketplace') && !auth('customer')->check())
    <div class="row g-0">
        <div class="col-12">
            <div class="fw-normal fs-6">
                <span>{!! BaseHelper::clean($config['name'] ?: __('Become a Vendor?')) !!}</span>
                <a class="text-primary ps-2" href="{{ route('customer.login') }}">{{ __('Register now') }}</a>
            </div>
        </div>
    </div>
@endif
