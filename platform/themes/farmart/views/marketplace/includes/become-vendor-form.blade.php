<div class="mb-3">
    <label class="form-label required" for="shop-name-register">{{ __('Shop Name') }}</label>
    <input class="form-control @if ($errors->has('shop_name')) is-invalid @endif"
        id="shop-name-register" type="text" name="shop_name" placeholder="{{ __('Store Name') }}" value="{{ old('shop_name') }}">
    @if ($errors->has('shop_name'))
        <div class="invalid-feedback">{{ $errors->first('shop_name') }}</div>
    @endif
</div>
<div class="form-group mb-3 position-relative">
    <label class="form-label required" for="shop-url-register">{{ __('Shop URL') }}</label>
    <input class="form-control @if ($errors->has('shop_url')) is-invalid @endif" id="shop-url-register" type="text"
        name="shop_url"
        placeholder="{{ __('Store URL') }}" value="{{ old('shop_url') }}"
        data-url="{{ route('public.ajax.check-store-url') }}">
    @if ($errors->has('shop_url'))
        <div class="invalid-feedback">{{ $errors->first('shop_url') }}</div>
    @else
        <span class="d-inline-block">
            <small data-base-url="{{ route('public.store', '') }}">{{ route('public.store', (string)old('shop_url')) }}</small>
        </span>
    @endif
    <span class="position-absolute top-0 end-0 shop-url-status"></span>
</div>
<div class="mb-3">
    <label class="form-label required" for="shop-phone-register">{{ __('Phone Number') }}</label>
    <input class="form-control @if ($errors->has('shop_phone')) is-invalid @endif" id="shop-phone-register" type="text" name="shop_phone"
        placeholder="{{ __('Ex: 0943243332') }}">
    @if ($errors->has('shop_phone'))
        <div class="invalid-feedback">{{ $errors->first('shop_phone') }}</div>
    @endif
</div>
