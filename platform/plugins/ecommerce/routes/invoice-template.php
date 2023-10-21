<?php

use Botble\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::prefix('invoice-template')->name('invoice-template.')->group(function () {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'InvoiceTemplateController@index',
                'permission' => 'ecommerce.invoice-template.index',
            ]);

            Route::put('/', [
                'as' => 'update',
                'uses' => 'InvoiceTemplateController@update',
                'permission' => 'ecommerce.invoice-template.index',
                'middleware' => 'preventDemo',
            ]);

            Route::post('reset', [
                'as' => 'reset',
                'uses' => 'InvoiceTemplateController@reset',
                'permission' => 'ecommerce.invoice-template.index',
                'middleware' => 'preventDemo',
            ]);

            Route::get('preview', [
                'as' => 'preview',
                'uses' => 'InvoiceTemplateController@preview',
                'permission' => 'ecommerce.invoice-template.index',
            ]);
        });
    });
});
