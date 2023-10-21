<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\Form;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\FormHelper;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Store;
use Botble\Media\Facades\RvMedia;
use Botble\Menu\Facades\Menu;
use Botble\SimpleSlider\Models\SimpleSliderItem;
use Botble\SocialLogin\Facades\SocialService;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;
use Theme\Farmart\Fields\ThemeIconField;

register_page_template([
    'default' => __('Default'),
    'homepage' => __('Homepage'),
    'full-width' => __('Full Width'),
    'coming-soon' => __('Coming Soon'),
]);

register_sidebar([
    'id' => 'pre_footer_sidebar',
    'name' => __('Top footer sidebar'),
    'description' => __('Widgets in the blog page'),
]);

register_sidebar([
    'id' => 'footer_sidebar',
    'name' => __('Footer sidebar'),
    'description' => __('Widgets in footer sidebar'),
]);

register_sidebar([
    'id' => 'bottom_footer_sidebar',
    'name' => __('Bottom footer sidebar'),
    'description' => __('Widgets in bottom footer sidebar'),
]);

if (is_plugin_active('ecommerce')) {
    register_sidebar([
        'id' => 'products_list_sidebar',
        'name' => __('Products list sidebar'),
        'description' => __('Widgets on header products list page'),
    ]);

    register_sidebar([
        'id' => 'product_detail_sidebar',
        'name' => __('Product detail sidebar'),
        'description' => __('Widgets in the product detail page'),
    ]);
}

RvMedia::setUploadPathAndURLToPublic();
RvMedia::addSize('small', 300, 300);

Menu::addMenuLocation('header-navigation', __('Header Navigation'));

function available_socials_store(): array
{
    return [
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'instagram' => 'Instagram',
        'youtube' => 'Youtube',
        'linkedin' => 'Linkedin',
    ];
}

app()->booted(function () {
    add_filter('marketplace_vendor_settings_register_content_tabs', function ($html, $object) {
        if (get_class($object) == Store::class) {
            return $html . view(Theme::getThemeNamespace() . '::views.marketplace.includes.extended-info-tab')->render();
        }

        return $html;
    }, 24, 2);

    add_filter('marketplace_vendor_settings_register_content_tab_inside', function ($html, $object) {
        if (get_class($object) == Store::class) {
            $background = $object->getMetaData('background', true);
            $socials = [];
            $availableSocials = [];
            if (! MarketplaceHelper::hideStoreSocialLinks()) {
                $socials = $object->getMetaData('socials', true);
                $availableSocials = available_socials_store();
            }
            $view = Theme::getThemeNamespace() . '::views.marketplace.includes.extended-info-content';

            return $html . view($view, compact('background', 'socials', 'availableSocials'))->render();
        }

        return $html;
    }, 24, 2);

    add_action([BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT], function ($type, $request, $object) {
        switch (get_class($object)) {
            case Store::class:

                if (request()->segment(1) === BaseHelper::getAdminPrefix()) {
                    if ($object->getMetaData('background', true) != $request->input('background')) {
                        MetaBox::saveMetaBoxData($object, 'background', $request->input('background'));
                    }
                } elseif (Route::currentRouteName() == 'marketplace.vendor.settings.post') {
                    if ($request->hasFile('background_input')) {
                        $result = RvMedia::handleUpload($request->file('background_input'), 0, 'stores');
                        if (! $result['error']) {
                            $file = $result['data'];
                            MetaBox::saveMetaBoxData($object, 'background', $file->url);
                        }
                    }
                }

                if (! MarketplaceHelper::hideStoreSocialLinks() && $request->has('socials')) {
                    $availableSocials = available_socials_store();
                    $socials = collect((array)$request->input('socials', []))->filter(function ($value, $key) use ($availableSocials) {
                        return filter_var($value, FILTER_VALIDATE_URL) && in_array($key, array_keys($availableSocials));
                    });

                    MetaBox::saveMetaBoxData($object, 'socials', $socials);
                }

                break;

            case SimpleSliderItem::class:
                if ($request->has('tablet_image')) {
                    MetaBox::saveMetaBoxData($object, 'tablet_image', $request->input('tablet_image'));
                }

                if ($request->has('mobile_image')) {
                    MetaBox::saveMetaBoxData($object, 'mobile_image', $request->input('mobile_image'));
                }

                break;
        }
    }, 230, 3);

    add_filter(BASE_FILTER_AFTER_FORM_CREATED, function ($form, $data) {
        if (get_class($data) == Store::class && request()->segment(1) === BaseHelper::getAdminPrefix()) {
            $form->addAfter('logo', 'background', 'mediaImage', [
                'label' => __('Background'),
                'label_attr' => ['class' => 'control-label'],
                'value' => $data->getMetaData('background', true),
            ]);
        }

        return $form;
    }, 128, 2);

    if (is_plugin_active('social-login')) {
        $data = SocialService::getModule('customer');
        if ($data) {
            $data['view'] = Theme::getThemeNamespace('partials.social-login-options');
            $data['use_css'] = false;
            SocialService::registerModule($data);
        }
    }
});

Form::component('themeIcon', Theme::getThemeNamespace() . '::partials.forms.icons-field', [
    'name',
    'value' => null,
    'attributes' => [],
]);

add_filter('form_custom_fields', function (FormAbstract $form, FormHelper $formHelper) {
    if (! $formHelper->hasCustomField('themeIcon')) {
        $form->addCustomField('themeIcon', ThemeIconField::class);
    }

    return $form;
}, 29, 2);

add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form, $data) {
    if (get_class($data) == SimpleSliderItem::class) {
        $form
            ->remove('description')
            ->addAfter('image', 'tablet_image', 'mediaImage', [
                'label' => __('Tablet Image'),
                'label_attr' => ['class' => 'control-label'],
                'value' => $data->getMetaData('tablet_image', true),
                'help_block' => [
                    'text' => __('For devices with width from 768px to 1200px, if empty, will use the image from the desktop.'),
                ],
            ])
            ->addAfter('tablet_image', 'mobile_image', 'mediaImage', [
                'label' => __('Mobile Image'),
                'label_attr' => ['class' => 'control-label'],
                'value' => $data->getMetaData('mobile_image', true),
                'help_block' => [
                    'text' => __('For devices with width less than 768px, if empty, will use the image from the tablet.'),
                ],
            ]);
    }

    return $form;
}, 127, 3);

if (! function_exists('theme_get_autoplay_speed_options')) {
    function theme_get_autoplay_speed_options(): array
    {
        $options = [2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000];

        return array_combine($options, $options);
    }
}

add_action('init', function () {
    EmailHandler::addTemplateSettings(Theme::getThemeName(), [
        'name' => __('Theme emails'),
        'description' => __('Config email templates for theme'),
        'templates' => [
            'contact-seller' => [
                'title' => __('Contact Seller'),
                'description' => __('Email will be sent to the seller when someone contact from store profile page'),
                'subject' => __('Message sent via your market profile on {{ site_title }}'),
                'can_off' => true,
            ],
        ],
        'variables' => [
            'contact_message' => __('Contact seller message'),
            'customer_name' => __('Customer Name'),
            'customer_email' => __('Customer Email'),
        ],
    ], 'themes');
}, 125);

if (! function_exists('get_store_list_layouts')) {
    function get_store_list_layouts(): array
    {
        return [
            'grid' => __('Grid'),
            'list' => __('List'),
        ];
    }
}
