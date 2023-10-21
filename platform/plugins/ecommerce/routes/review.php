<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Product;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'reviews', 'as' => 'reviews.'], function () {
            Route::match(['GET', 'POST'], '/', [
                'as' => 'index',
                'uses' => 'ReviewController@index',
                'permission' => 'reviews.index',
            ]);

            Route::get('{review}', [
                'as' => 'show',
                'uses' => 'ReviewController@show',
                'permission' => 'reviews.index',
            ]);

            Route::delete('{review}', [
                'as' => 'destroy',
                'uses' => 'ReviewController@destroy',
                'permission' => 'reviews.destroy',
            ]);

            Route::post('{review}/publish', [
                'as' => 'publish',
                'uses' => 'PublishedReviewController@store',
                'permission' => 'reviews.publish',
            ]);

            Route::post('{review}/unpublish', [
                'as' => 'unpublish',
                'uses' => 'PublishedReviewController@destroy',
                'permission' => 'reviews.publish',
            ]);
        });
    });
});

Route::group([
    'namespace' => 'Botble\Ecommerce\Http\Controllers\Fronts',
    'middleware' => ['web', 'core', 'customer'],
], function () {
    Route::group(apply_filters(BASE_FILTER_GROUP_PUBLIC_ROUTE, []), function () {
        Route::post('review/create', [
            'as' => 'public.reviews.create',
            'uses' => 'ReviewController@store',
        ]);

        Route::delete('review/delete/{id}', [
            'as' => 'public.reviews.destroy',
            'uses' => 'ReviewController@destroy',
        ])->wherePrimaryKey();

        Route::get(SlugHelper::getPrefix(Product::class, 'products') . '/{slug}/review', [
            'uses' => 'ReviewController@getProductReview',
            'as' => 'public.product.review',
            'middleware' => 'customer',
        ]);
    });
});
