<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix() . '/ecommerce', 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
            Route::resource('', 'ProductController')
                ->parameters(['' => 'product']);

            Route::post('{product}/duplicate', [
                'as' => 'duplicate',
                'uses' => 'ProductController@duplicate',
                'permission' => 'products.duplicate',
            ]);

            Route::post('add-attribute-to-product/{id}', [
                'as' => 'add-attribute-to-product',
                'uses' => 'ProductController@postAddAttributeToProduct',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();

            Route::post('delete-version/{id}', [
                'as' => 'delete-version',
                'uses' => 'ProductController@deleteVersion',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();

            Route::delete('items/delete-versions', [
                'as' => 'delete-versions',
                'uses' => 'ProductController@deleteVersions',
                'permission' => 'products.edit',
            ]);

            Route::post('add-version/{id}', [
                'as' => 'add-version',
                'uses' => 'ProductController@postAddVersion',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();

            Route::get('get-version-form/{id?}', [
                'as' => 'get-version-form',
                'uses' => 'ProductController@getVersionForm',
                'permission' => 'products.edit',
            ]);

            Route::post('update-version/{id}', [
                'as' => 'update-version',
                'uses' => 'ProductController@postUpdateVersion',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();

            Route::post('generate-all-version/{id}', [
                'as' => 'generate-all-versions',
                'uses' => 'ProductController@postGenerateAllVersions',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();

            Route::post('store-related-attributes/{id}', [
                'as' => 'store-related-attributes',
                'uses' => 'ProductController@postStoreRelatedAttributes',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();

            Route::post('save-all-version/{id}', [
                'as' => 'save-all-versions',
                'uses' => 'ProductController@postSaveAllVersions',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();

            Route::get('get-list-product-for-search', [
                'as' => 'get-list-product-for-search',
                'uses' => 'ProductController@getListProductForSearch',
                'permission' => 'products.edit',
            ]);

            Route::get('get-relations-box/{id?}', [
                'as' => 'get-relations-boxes',
                'uses' => 'ProductController@getRelationBoxes',
                'permission' => 'products.edit',
            ]);

            Route::get('get-list-products-for-select', [
                'as' => 'get-list-products-for-select',
                'uses' => 'ProductController@getListProductForSelect',
                'permission' => 'products.index',
            ]);

            Route::post('create-product-when-creating-order', [
                'as' => 'create-product-when-creating-order',
                'uses' => 'ProductController@postCreateProductWhenCreatingOrder',
                'permission' => 'products.create',
            ]);

            Route::get('get-all-products-and-variations', [
                'as' => 'get-all-products-and-variations',
                'uses' => 'ProductController@getAllProductAndVariations',
                'permission' => 'products.index',
            ]);

            Route::post('update-order-by', [
                'as' => 'update-order-by',
                'uses' => 'ProductController@postUpdateOrderby',
                'permission' => 'products.edit',
            ]);

            Route::post('product-variations/{id}', [
                'as' => 'product-variations',
                'uses' => 'ProductController@getProductVariations',
                'permission' => 'products.index',
            ])->wherePrimaryKey();

            Route::get('product-attribute-sets/{id?}', [
                'as' => 'product-attribute-sets',
                'uses' => 'ProductController@getProductAttributeSets',
                'permission' => 'products.index',
            ])->wherePrimaryKey();

            Route::post('set-default-product-variation/{id}', [
                'as' => 'set-default-product-variation',
                'uses' => 'ProductController@setDefaultProductVariation',
                'permission' => 'products.edit',
            ])->wherePrimaryKey();
        });
    });
});
