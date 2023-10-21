{!! BaseHelper::googleFonts('https://fonts.googleapis.com/css2?family=' . urlencode(theme_option('primary_font', 'Muli')) . ':wght@400;600;700&display=swap') !!}

{!! Assets::renderHeader(['core']) !!}

<link rel="stylesheet" href="{{ asset('vendor/core/core/base/css/themes/default.css') }}?v={{ get_cms_version() }}">

<link rel="stylesheet" href="{{ Theme::asset()->url('fonts/Linearicons/Linearicons/Font/demo-files/demo.css') }}?v={{ MarketplaceHelper::getAssetVersion() }}">
<link rel="stylesheet" href="{{ Theme::asset()->url('css/marketplace.css') }}?v={{ MarketplaceHelper::getAssetVersion() }}">

@if (BaseHelper::siteLanguageDirection() == 'rtl')
    <link rel="stylesheet" href="{{ asset('vendor/core/core/base/css/rtl.css') }}?v={{ get_cms_version() }}">
    <link rel="stylesheet" href="{{ Theme::asset()->url('css/marketplace-rtl.css') }}?v={{ MarketplaceHelper::getAssetVersion() }}">
@endif
