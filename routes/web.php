<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SectionController;
use App\Http\Middleware\SetTimezone;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('app_login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('app_logout');

Route::middleware(['auth', SetTimezone::class])->group(function () {
    Route::match(['GET', 'POST'], '/', [ItemController::class, 'index'])->name('app_index');
    Route::match(['GET', 'POST'], '/items/bulk-add', [ItemController::class, 'itemBulkAdd'])->name('app_item_bulk_add');
    Route::match(['GET', 'POST'], '/items/edit', [ItemController::class, 'itemEdit'])->name('app_item_edit');
    Route::match(['GET', 'POST'], '/items/prioritize', [ItemController::class, 'itemPrioritize'])->name('app_item_prioritize');
    Route::match(['GET', 'POST'], '/sections', [SectionController::class, 'sectionEdit'])->name('app_section_edit');
    Route::match(['GET', 'POST'], '/account', [AccountController::class, 'account'])->name('app_account');
    Route::get('/history', [HistoryController::class, 'showDone'])->name('app_show_done');
});
