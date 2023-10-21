<?php

use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;
use Theme\Farmart\Http\Controllers\FarmartController;

Route::group(['controller' => FarmartController::class, 'middleware' => ['web', 'core']], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
        Route::group(['prefix' => 'ajax', 'as' => 'public.ajax.'], function () {
            Route::get('search-products', [
                'uses' => 'ajaxSearchProducts',
                'as' => 'search-products',
            ]);

            Route::get('cart', [
                'uses' => 'ajaxCart',
                'as' => 'cart',
            ]);

            Route::get('quick-view/{id?}', [
                'uses' => 'ajaxGetQuickView',
                'as' => 'quick-view',
            ])->wherePrimaryKey();

            Route::post('add-to-wishlist/{id?}', [
                'uses' => 'ajaxAddProductToWishlist',
                'as' => 'add-to-wishlist',
            ])->wherePrimaryKey();

            Route::get('product-reviews/{id}', [
                'uses' => 'ajaxGetProductReviews',
                'as' => 'product-reviews',
            ])->wherePrimaryKey();

            Route::get('recently-viewed-products', [
                'uses' => 'ajaxGetRecentlyViewedProducts',
                'as' => 'recently-viewed-products',
            ]);

            Route::post('ajax/contact-seller', 'ajaxContactSeller')
                ->name('contact-seller');

            Route::get('products-by-collection/{id}', 'ajaxGetProductsByCollection')
                ->name('products-by-collection')
                ->wherePrimaryKey();

            Route::get('products-by-category/{id}', 'ajaxGetProductsByCategory')
                ->name('products-by-category')
                ->wherePrimaryKey();
        });
    });
});

Theme::routes();
