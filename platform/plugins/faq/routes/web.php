<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Faq\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'faq-categories', 'as' => 'faq_category.'], function () {
            Route::resource('', 'FaqCategoryController')->parameters(['' => 'faq_category']);
        });

        Route::group(['prefix' => 'faqs', 'as' => 'faq.'], function () {
            Route::resource('', 'FaqController')->parameters(['' => 'faq']);
        });
    });
});
