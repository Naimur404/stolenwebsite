{!! Assets::renderHeader(['core']) !!}

<link rel="stylesheet" href="{{ asset('vendor/core/core/base/css/themes/default.css') }}?v={{ get_cms_version() }}">
<link rel="stylesheet" href="{{ asset('vendor/core/plugins/marketplace/css/vendors/normalize.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/core/plugins/marketplace/css/vendors/material-icon-round.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/core/plugins/marketplace/css/vendors/perfect-scrollbar.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/core/plugins/marketplace/css/style.css') }}?v={{ MarketplaceHelper::getAssetVersion() }}">

@if (BaseHelper::siteLanguageDirection() == 'rtl')
    <link rel="stylesheet" href="{{ asset('vendor/core/core/base/css/rtl.css') }}?v={{ get_cms_version() }}">
    <link rel="stylesheet" href="{{ asset('vendor/core/plugins/marketplace/css/rtl.css') }}?v={{ MarketplaceHelper::getAssetVersion() }}">
@endif
