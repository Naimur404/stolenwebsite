<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Brand;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\ProductTag;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix() . '/ecommerce', 'middleware' => 'auth'], function () {
        Route::get('settings', [
            'as' => 'ecommerce.settings',
            'uses' => 'EcommerceController@getSettings',
        ]);

        Route::post('settings', [
            'as' => 'ecommerce.settings.post',
            'uses' => 'EcommerceController@postSettings',
            'permission' => 'ecommerce.settings',
        ]);

        Route::post('update-currencies-from-exchange-api', [
            'as' => 'ecommerce.setting.update-currencies-from-exchange-api',
            'uses' => 'EcommerceController@updateCurrenciesFromExchangeApi',
            'permission' => 'ecommerce.settings',
        ]);

        Route::post('clear-cache-currency-rates', [
            'as' => 'ecommerce.setting.clear-cache-currency-rates',
            'uses' => 'EcommerceController@clearCacheCurrencyRates',
            'permission' => 'ecommerce.settings',
        ]);

        Route::get('advanced-settings', [
            'as' => 'ecommerce.advanced-settings',
            'uses' => 'EcommerceController@getAdvancedSettings',
            'permission' => 'ecommerce.settings',
        ]);

        Route::post('advanced-settings', [
            'as' => 'ecommerce.advanced-settings.post',
            'uses' => 'EcommerceController@postAdvancedSettings',
            'permission' => 'ecommerce.settings',
        ]);

        Route::get('tracking-settings', [
            'as' => 'ecommerce.tracking-settings',
            'uses' => 'EcommerceController@getTrackingSettings',
            'permission' => 'ecommerce.settings',
        ]);

        Route::post('tracking-settings', [
            'as' => 'ecommerce.tracking-settings.post',
            'uses' => 'EcommerceController@postTrackingSettings',
            'permission' => 'ecommerce.settings',
        ]);

        Route::get('ajax/countries', [
            'as' => 'ajax.countries.list',
            'uses' => 'EcommerceController@ajaxGetCountries',
            'permission' => false,
        ]);

        Route::get('store-locators/form/{id?}', [
            'as' => 'ecommerce.store-locators.form',
            'uses' => 'EcommerceController@getStoreLocatorForm',
            'permission' => 'ecommerce.settings',
        ]);

        Route::post('store-locators/edit/{id}', [
            'as' => 'ecommerce.store-locators.edit.post',
            'uses' => 'EcommerceController@postUpdateStoreLocator',
            'permission' => 'ecommerce.settings',
        ])->wherePrimaryKey();

        Route::post('store-locators/create', [
            'as' => 'ecommerce.store-locators.create',
            'uses' => 'EcommerceController@postCreateStoreLocator',
            'permission' => 'ecommerce.settings',
        ]);

        Route::post('store-locators/delete/{id}', [
            'as' => 'ecommerce.store-locators.destroy',
            'uses' => 'EcommerceController@postDeleteStoreLocator',
            'permission' => 'ecommerce.settings',
        ])->wherePrimaryKey();

        Route::post('store-locators/update-primary-store', [
            'as' => 'ecommerce.store-locators.update-primary-store',
            'uses' => 'EcommerceController@postUpdatePrimaryStore',
            'permission' => 'ecommerce.settings',
        ]);

        Route::group(['prefix' => 'product-categories', 'as' => 'product-categories.'], function () {
            Route::resource('', 'ProductCategoryController')
                ->parameters(['' => 'product_category']);

            Route::get('search', [
                'as' => 'search',
                'uses' => 'ProductCategoryController@getSearch',
                'permission' => 'product-categories.index',
            ]);
        });

        Route::group(['prefix' => 'product-tags', 'as' => 'product-tag.'], function () {
            Route::resource('', 'ProductTagController')
                ->parameters(['' => 'product-tag']);

            Route::get('all', [
                'as' => 'all',
                'uses' => 'ProductTagController@getAllTags',
                'permission' => 'product-tag.index',
            ]);
        });

        Route::group(['prefix' => 'options', 'as' => 'global-option.'], function () {
            Route::resource('', 'ProductOptionController')->parameters(['' => 'option']);

            Route::get('ajax', [
                'as' => 'ajaxInfo',
                'uses' => 'ProductOptionController@ajaxInfo',
                'permission' => 'products.edit',
            ]);
        });

        Route::group(['prefix' => 'brands', 'as' => 'brands.'], function () {
            Route::resource('', 'BrandController')
                ->parameters(['' => 'brand']);
        });

        Route::group(['prefix' => 'product-collections', 'as' => 'product-collections.'], function () {
            Route::resource('', 'ProductCollectionController')
                ->parameters(['' => 'product_collection']);

            Route::get('get-list-product-collections-for-select', [
                'as' => 'get-list-product-collections-for-select',
                'uses' => 'ProductCollectionController@getListForSelect',
                'permission' => 'product-collections.index',
            ]);

            Route::get('get-product-collection/{id?}', [
                'as' => 'get-product-collection',
                'uses' => 'ProductCollectionController@getProductCollection',
                'permission' => 'product-collections.edit',
            ])->wherePrimaryKey();
        });

        Route::group(['prefix' => 'product-attribute-sets', 'as' => 'product-attribute-sets.'], function () {
            Route::resource('', 'ProductAttributeSetsController')
                ->parameters(['' => 'product_attribute_set']);
        });

        Route::group(['prefix' => 'reports'], function () {
            Route::get('', [
                'as' => 'ecommerce.report.index',
                'uses' => 'ReportController@getIndex',
            ]);

            Route::post('top-selling-products', [
                'as' => 'ecommerce.report.top-selling-products',
                'uses' => 'ReportController@getTopSellingProducts',
                'permission' => 'ecommerce.report.index',
            ]);

            Route::post('recent-orders', [
                'as' => 'ecommerce.report.recent-orders',
                'uses' => 'ReportController@getRecentOrders',
                'permission' => 'ecommerce.report.index',
            ]);

            Route::post('trending-products', [
                'as' => 'ecommerce.report.trending-products',
                'uses' => 'ReportController@getTrendingProducts',
                'permission' => 'ecommerce.report.index',
            ]);

            Route::get('dashboard-general-report', [
                'as' => 'ecommerce.report.dashboard-widget.general',
                'uses' => 'ReportController@getDashboardWidgetGeneral',
                'permission' => 'ecommerce.report.index',
            ]);
        });

        Route::group(['prefix' => 'flash-sales', 'as' => 'flash-sale.'], function () {
            Route::resource('', 'FlashSaleController')->parameters(['' => 'flash-sale']);
        });

        Route::group(['prefix' => 'product-labels', 'as' => 'product-label.'], function () {
            Route::resource('', 'ProductLabelController')->parameters(['' => 'product-label']);
        });

        Route::group(['prefix' => 'import', 'as' => 'ecommerce.import.'], function () {
            Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
                Route::get('/', [
                    'as' => 'index',
                    'uses' => 'BulkImportController@index',
                ]);

                Route::post('/', [
                    'as' => 'index.post',
                    'uses' => 'BulkImportController@postImport',
                    'permission' => 'ecommerce.import.products.index',
                ]);

                Route::post('/download-template', [
                    'as' => 'download-template',
                    'uses' => 'BulkImportController@downloadTemplate',
                    'permission' => 'ecommerce.import.products.index',
                ]);
            });
        });

        Route::group(['prefix' => 'export', 'as' => 'ecommerce.export.'], function () {
            Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
                Route::get('/', [
                    'as' => 'index',
                    'uses' => 'ExportController@products',
                ]);

                Route::post('/', [
                    'as' => 'index.post',
                    'uses' => 'ExportController@exportProducts',
                    'permission' => 'ecommerce.export.products.index',
                ]);
            });
        });
    });
});

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers\Fronts', 'middleware' => ['web', 'core']], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
        Route::get(SlugHelper::getPrefix(Product::class, 'products'), [
            'uses' => 'PublicProductController@getProducts',
            'as' => 'public.products',
        ]);

        Route::get(SlugHelper::getPrefix(Brand::class, 'brands') . '/{slug}', [
            'uses' => 'PublicProductController@getBrand',
            'as' => 'public.brand',
        ]);

        Route::get(SlugHelper::getPrefix(Product::class, 'products') . '/{slug}', [
            'uses' => 'PublicProductController@getProduct',
            'as' => 'public.product',
        ]);

        Route::get(SlugHelper::getPrefix(ProductCategory::class, 'product-categories') . '/{slug}', [
            'uses' => 'PublicProductController@getProductCategory',
            'as' => 'public.product-category',
        ]);

        Route::get(SlugHelper::getPrefix(ProductTag::class, 'product-tags') . '/{slug}', [
            'uses' => 'PublicProductController@getProductTag',
            'as' => 'public.product-tag',
        ]);

        Route::get('currency/switch/{code?}', [
            'as' => 'public.change-currency',
            'uses' => 'PublicEcommerceController@changeCurrency',
        ]);

        Route::get('product-variation/{id}', [
            'as' => 'public.web.get-variation-by-attributes',
            'uses' => 'PublicProductController@getProductVariation',
        ])->wherePrimaryKey();

        Route::get('orders/tracking', [
            'as' => 'public.orders.tracking',
            'uses' => 'PublicProductController@getOrderTracking',
        ])->wherePrimaryKey();
    });
});
