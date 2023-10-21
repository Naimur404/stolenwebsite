<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Marketplace\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'marketplaces', 'as' => 'marketplace.'], function () {
            Route::group(['prefix' => 'stores', 'as' => 'store.'], function () {
                Route::resource('', 'StoreController')->parameters(['' => 'store']);

                Route::get('view/{id}', [
                    'as' => 'view',
                    'uses' => 'StoreRevenueController@view',
                ])->wherePrimaryKey();

                Route::group(['prefix' => 'revenues', 'as' => 'revenue.'], function () {
                    Route::match(['GET', 'POST'], 'list/{id}', [
                        'as' => 'index',
                        'uses' => 'StoreRevenueController@index',
                        'permission' => 'marketplace.store.view',
                    ])->wherePrimaryKey();

                    Route::post('create/{id}', [
                        'as' => 'create',
                        'uses' => 'StoreRevenueController@store',
                    ])->wherePrimaryKey();
                });
            });

            Route::group(['prefix' => 'withdrawals', 'as' => 'withdrawal.'], function () {
                Route::resource('', 'WithdrawalController')
                    ->parameters(['' => 'withdrawal'])
                    ->except([
                        'create',
                        'store',
                    ]);
            });

            Route::get('settings', [
                'as' => 'settings',
                'uses' => 'MarketplaceController@getSettings',
            ]);

            Route::post('settings', [
                'as' => 'settings.post',
                'uses' => 'MarketplaceController@postSettings',
                'permission' => 'marketplace.settings',
            ]);

            Route::group(['prefix' => 'unverified-vendors', 'as' => 'unverified-vendors.'], function () {
                Route::match(['GET', 'POST'], '/', [
                    'as' => 'index',
                    'uses' => 'UnverifiedVendorController@index',
                ]);

                Route::get('view/{id}', [
                    'as' => 'view',
                    'uses' => 'UnverifiedVendorController@view',
                    'permission' => 'marketplace.unverified-vendors.edit',
                ])->wherePrimaryKey();

                Route::post('approve/{id}', [
                    'as' => 'approve-vendor',
                    'uses' => 'UnverifiedVendorController@approveVendor',
                    'permission' => 'marketplace.unverified-vendors.edit',
                ])->wherePrimaryKey();
            });

            Route::group(['prefix' => 'vendors', 'as' => 'vendors.'], function () {
                Route::match(['GET', 'POST'], '/', [
                    'as' => 'index',
                    'uses' => 'VendorController@index',
                ]);
            });

            Route::group([
                'prefix' => 'reports',
                'as' => 'reports.',
                'permission' => 'marketplace.reports',
                'controller' => 'ReportController',
            ], function () {
                Route::get('', [
                    'as' => 'index',
                    'uses' => 'index',
                ]);

                Route::post('store-revenues', [
                    'as' => 'store-revenues',
                    'uses' => 'getStoreRevenues',
                ]);
            });
        });

        Route::group(['prefix' => 'ecommerce/products', 'as' => 'products.'], function () {
            Route::post('approve-product/{id}', [
                'as' => 'approve-product',
                'uses' => 'ProductController@approveProduct',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();
        });
    });
});
