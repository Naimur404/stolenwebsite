<?php

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Marketplace\Models\Store;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Botble\Marketplace\Http\Controllers\Fronts',
    'middleware' => ['web', 'core'],
], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
        Route::get(SlugHelper::getPrefix(Store::class, 'stores'), [
            'as' => 'public.stores',
            'uses' => 'PublicStoreController@getStores',
        ]);

        Route::get(SlugHelper::getPrefix(Store::class, 'stores') . '/{slug}', [
            'uses' => 'PublicStoreController@getStore',
            'as' => 'public.store',
        ]);

        Route::post('ajax/stores/check-store-url', [
            'as' => 'public.ajax.check-store-url',
            'uses' => 'PublicStoreController@checkStoreUrl',
        ]);

        Route::group([
            'prefix' => 'vendor',
            'as' => 'marketplace.vendor.',
            'middleware' => ['vendor'],
        ], function () {
            Route::group(['prefix' => 'ajax'], function () {
                Route::post('upload', [
                    'as' => 'upload',
                    'uses' => 'DashboardController@postUpload',
                ]);

                Route::post('upload-from-editor', [
                    'as' => 'upload-from-editor',
                    'uses' => 'DashboardController@postUploadFromEditor',
                ]);

                Route::group(['prefix' => 'chart', 'as' => 'chart.'], function () {
                    Route::get('month', [
                        'as' => 'month',
                        'uses' => 'RevenueController@getMonthChart',
                    ]);
                });
            });

            Route::get('dashboard', [
                'as' => 'dashboard',
                'uses' => 'DashboardController@index',
            ]);

            Route::get('settings', [
                'as' => 'settings',
                'uses' => 'SettingController@index',
            ]);

            Route::post('settings', [
                'as' => 'settings.post',
                'uses' => 'SettingController@saveSettings',
            ]);

            Route::resource('revenues', 'RevenueController')
                ->parameters(['' => 'revenue'])
                ->only(['index']);

            Route::get('statements', fn () => to_route('marketplace.vendor.revenues.index'))
                ->name('statements.index');

            Route::resource('withdrawals', 'WithdrawalController')
                ->parameters(['' => 'withdrawal'])
                ->only([
                    'index',
                    'create',
                    'store',
                    'edit',
                    'update',
                ]);

            Route::group(['prefix' => 'withdrawals'], function () {
                Route::get('show/{id}', [
                    'as' => 'withdrawals.show',
                    'uses' => 'WithdrawalController@show',
                ])->wherePrimaryKey();
            });

            if (EcommerceHelper::isReviewEnabled()) {
                Route::resource('reviews', 'ReviewController')
                    ->parameters(['' => 'review'])
                    ->only(['index']);
            }

            Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
                Route::resource('', 'ProductController')
                    ->parameters(['' => 'product']);

                Route::post('add-attribute-to-product/{id}', [
                    'as' => 'add-attribute-to-product',
                    'uses' => 'ProductController@postAddAttributeToProduct',
                ])->wherePrimaryKey();

                Route::post('delete-version/{id}', [
                    'as' => 'delete-version',
                    'uses' => 'ProductController@deleteVersion',
                ])->wherePrimaryKey();

                Route::post('add-version/{id}', [
                    'as' => 'add-version',
                    'uses' => 'ProductController@postAddVersion',
                ])->wherePrimaryKey();

                Route::get('get-version-form/{id?}', [
                    'as' => 'get-version-form',
                    'uses' => 'ProductController@getVersionForm',
                ]);

                Route::post('update-version/{id}', [
                    'as' => 'update-version',
                    'uses' => 'ProductController@postUpdateVersion',
                ])->wherePrimaryKey();

                Route::post('generate-all-version/{id}', [
                    'as' => 'generate-all-versions',
                    'uses' => 'ProductController@postGenerateAllVersions',
                ])->wherePrimaryKey();

                Route::post('store-related-attributes/{id}', [
                    'as' => 'store-related-attributes',
                    'uses' => 'ProductController@postStoreRelatedAttributes',
                ]);

                Route::post('save-all-version/{id}', [
                    'as' => 'save-all-versions',
                    'uses' => 'ProductController@postSaveAllVersions',
                ])->wherePrimaryKey();

                Route::get('get-list-product-for-search', [
                    'as' => 'get-list-product-for-search',
                    'uses' => 'ProductController@getListProductForSearch',
                ]);

                Route::get('get-relations-box/{id?}', [
                    'as' => 'get-relations-boxes',
                    'uses' => 'ProductController@getRelationBoxes',
                ]);

                Route::get('get-list-products-for-select', [
                    'as' => 'get-list-products-for-select',
                    'uses' => 'ProductController@getListProductForSelect',
                ]);

                Route::post('create-product-when-creating-order', [
                    'as' => 'create-product-when-creating-order',
                    'uses' => 'ProductController@postCreateProductWhenCreatingOrder',
                ]);

                Route::get('get-all-products-and-variations', [
                    'as' => 'get-all-products-and-variations',
                    'uses' => 'ProductController@getAllProductAndVariations',
                ]);

                Route::post('update-order-by', [
                    'as' => 'update-order-by',
                    'uses' => 'ProductController@postUpdateOrderby',
                ]);

                Route::post('product-variations/{id}', [
                    'as' => 'product-variations',
                    'uses' => 'ProductController@getProductVariations',
                ])->wherePrimaryKey();

                Route::get('product-attribute-sets/{id?}', [
                    'as' => 'product-attribute-sets',
                    'uses' => 'ProductController@getProductAttributeSets',
                ])->wherePrimaryKey();

                Route::post('set-default-product-variation/{id}', [
                    'as' => 'set-default-product-variation',
                    'uses' => 'ProductController@setDefaultProductVariation',
                ])->wherePrimaryKey();
            });

            Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
                Route::resource('', 'OrderController')->parameters(['' => 'order'])->except(['create', 'store']);

                Route::get('generate-invoice/{id}', [
                    'as' => 'generate-invoice',
                    'uses' => 'OrderController@getGenerateInvoice',
                ])->wherePrimaryKey();

                Route::post('confirm', [
                    'as' => 'confirm',
                    'uses' => 'OrderController@postConfirm',
                ]);

                Route::post('send-order-confirmation-email/{id}', [
                    'as' => 'send-order-confirmation-email',
                    'uses' => 'OrderController@postResendOrderConfirmationEmail',
                ])->wherePrimaryKey();

                Route::post('update-shipping-address/{id}', [
                    'as' => 'update-shipping-address',
                    'uses' => 'OrderController@postUpdateShippingAddress',
                ])->wherePrimaryKey();

                Route::post('cancel-order/{id}', [
                    'as' => 'cancel',
                    'uses' => 'OrderController@postCancelOrder',
                ])->wherePrimaryKey();

                Route::post('update-shipping-status/{id}', [
                    'as' => 'update-shipping-status',
                    'uses' => 'ShipmentController@postUpdateStatus',
                ])->wherePrimaryKey();
            });

            Route::group(['prefix' => 'order-returns', 'as' => 'order-returns.'], function () {
                Route::resource('', 'OrderReturnController')->parameters(['' => 'order'])->except(['create', 'store']);
            });

            Route::group(['prefix' => 'shipments', 'as' => 'shipments.'], function () {
                Route::resource('', 'ShipmentController')
                    ->parameters(['' => 'shipment'])
                    ->except(['create', 'store']);

                Route::post('update-cod-status/{id}', [
                    'as' => 'update-cod-status',
                    'uses' => 'ShipmentController@postUpdateCodStatus',
                ])->wherePrimaryKey();
            });

            Route::group(['prefix' => 'coupons', 'as' => 'discounts.'], function () {
                Route::resource('', 'DiscountController')->parameters(['' => 'coupon'])->except(['edit', 'update']);

                Route::post('generate-coupon', [
                    'as' => 'generate-coupon',
                    'uses' => 'DiscountController@postGenerateCoupon',
                ]);
            });

            Route::get('ajax/product-options', [
                'as' => 'ajax-product-option-info',
                'uses' => 'ProductController@ajaxProductOptionInfo',
            ]);
        });

        Route::group([
            'prefix' => 'vendor',
            'as' => 'marketplace.vendor.',
            'middleware' => ['customer'],
        ], function () {
            Route::get('become-vendor', [
                'as' => 'become-vendor',
                'uses' => 'DashboardController@getBecomeVendor',
            ]);

            Route::post('become-vendor', [
                'as' => 'become-vendor.post',
                'uses' => 'DashboardController@postBecomeVendor',
            ]);
        });
    });
});

Route::group([
    'namespace' => 'Botble\Ecommerce\Http\Controllers',
    'middleware' => ['web', 'core'],
], function () {
    Route::group([
        'prefix' => 'vendor',
        'as' => 'marketplace.vendor.',
        'middleware' => ['vendor'],
    ], function () {
        Route::get('tags/all', [
            'as' => 'tags.all',
            'uses' => 'ProductTagController@getAllTags',
        ]);
    });
});
