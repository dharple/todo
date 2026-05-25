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

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'loginPost']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', SetTimezone::class])->group(function () {
    Route::get('/', [ItemController::class, 'index'])->name('index');
    Route::post('/', [ItemController::class, 'indexPost']);

    Route::get('/items/bulk-add', [ItemController::class, 'itemBulkAdd'])->name('item_bulk_add');
    Route::post('/items/bulk-add', [ItemController::class, 'itemBulkAddPost']);

    Route::get('/items/edit', [ItemController::class, 'itemEdit'])->name('item_edit');
    Route::post('/items/edit', [ItemController::class, 'itemEditPost']);

    Route::get('/items/prioritize', [ItemController::class, 'itemPrioritize'])->name('item_prioritize');
    Route::post('/items/prioritize', [ItemController::class, 'itemPriorizePost']);

    Route::get('/sections', [SectionController::class, 'sectionEdit'])->name('section_edit');
    Route::post('/sections', [SectionController::class, 'sectionEditPost']);

    Route::post('/account', [AccountController::class, 'accountPost'])->name('account_edit');

    Route::get('/password', [AccountController::class, 'password'])->name('password');
    Route::post('/password', [AccountController::class, 'passwordPost']);

    Route::get('/history', [HistoryController::class, 'showDone'])->name('show_done');
});
