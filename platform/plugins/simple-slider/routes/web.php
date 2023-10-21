<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\SimpleSlider\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'simple-sliders', 'as' => 'simple-slider.'], function () {
            Route::resource('', 'SimpleSliderController')->parameters(['' => 'simple-slider']);

            Route::post('sorting', [
                'as' => 'sorting',
                'uses' => 'SimpleSliderController@postSorting',
                'permission' => 'simple-slider.edit',
            ]);
        });

        Route::group(['prefix' => 'simple-slider-items', 'as' => 'simple-slider-item.'], function () {
            Route::resource('', 'SimpleSliderItemController')->except([
                'index',
            ])->parameters(['' => 'simple-slider-item']);

            Route::match(['GET', 'POST'], 'list/{id}', [
                'as' => 'index',
                'uses' => 'SimpleSliderItemController@index',
            ])->wherePrimaryKey();

            Route::get('delete/{id}', [
                'as' => 'destroy.get',
                'uses' => 'SimpleSliderItemController@getDelete',
                'permission' => 'simple-slider-item.destroy',
            ])->wherePrimaryKey();
        });
    });
});
