<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Table\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web', 'core', 'auth'],
    'prefix' => BaseHelper::getAdminPrefix() . '/tables',
    'permission' => false,
], function () {
    Route::get('bulk-change/data', [TableController::class, 'getDataForBulkChanges'])->name('tables.bulk-change.data');
    Route::post('bulk-change/save', [TableController::class, 'postSaveBulkChange'])->name('tables.bulk-change.save');
    Route::post('bulk-actions', [TableController::class, 'postDispatchBulkAction'])->name('tables.bulk-actions.dispatch');
    Route::get('get-filter-input', [TableController::class, 'getFilterInput'])->name('tables.get-filter-input');
});
