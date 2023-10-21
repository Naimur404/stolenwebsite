<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Page\Models\Page;
use Botble\Setting\Facades\Setting;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;

class ThemeOptionSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('general');

        $theme = Theme::getThemeName();

        $settingPrefix = 'theme-' . $theme . '-';

        Setting::newQuery()->where('key', 'LIKE', $settingPrefix . '%')->delete();

        $data = [
            'site_title' => 'Farmart - Laravel Ecommerce system',
            'seo_description' => 'Farmart is a modern and flexible Multipurpose Marketplace Laravel script. This script is suited for electronic, organic and grocery store, furniture store, clothing store, hitech store and accessories store… With the theme, you can create your own marketplace and allow vendors to sell just like Amazon, Envato, eBay…',
            'copyright' => sprintf('©%s Farmart. All Rights Reserved.', Carbon::now()->format('Y')),
            'favicon' => 'general/favicon.png',
            'logo' => 'general/logo.png',
            'seo_og_image' => 'general/open-graph-image.png',
            'image-placeholder' => 'general/placeholder.png',
            'address' => '502 New Street, Brighton VIC, Australia',
            'hotline' => '8 800 332 65-66',
            'email' => 'contact@fartmart.co',
            'working_time' => 'Mon - Fri: 07AM - 06PM',
            'payment_methods_image' => 'general/footer-payments.png',
            'homepage_id' => Page::query()->value('id'),
            'blog_page_id' => Page::query()->skip(5)->value('id'),
            'cookie_consent_message' => 'Your experience on this site will be improved by allowing cookies ',
            'cookie_consent_learn_more_url' => 'cookie-policy',
            'cookie_consent_learn_more_text' => 'Cookie Policy',
            'number_of_products_per_page' => 40,
            'number_of_cross_sale_product' => 6,
            'logo_in_the_checkout_page' => 'general/logo.png',
            'logo_in_invoices' => 'general/logo.png',
            'logo_vendor_dashboard' => 'general/logo.png',
            '404_page_image' => 'general/404.png',
        ];

        Setting::set($this->prepareThemeOptions($data));

        $socialLinks = [
            [
                [
                    'key' => 'social-name',
                    'value' => 'Facebook',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'general/facebook.png',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://www.facebook.com/',
                ],
            ],
            [
                [
                    'key' => 'social-name',
                    'value' => 'Twitter',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'general/twitter.png',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://www.twitter.com/',
                ],
            ],
            [
                [
                    'key' => 'social-name',
                    'value' => 'Instagram',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'general/instagram.png',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://www.instagram.com/',
                ],
            ],
            [
                [
                    'key' => 'social-name',
                    'value' => 'Pinterest',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'general/pinterest.png',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://www.pinterest.com/',
                ],
            ],
            [
                [
                    'key' => 'social-name',
                    'value' => 'Youtube',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'general/youtube.png',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://www.youtube.com/',
                ],
            ],
        ];

        Setting::set($settingPrefix . 'social_links', json_encode($socialLinks));

        Setting::save();
    }
}
