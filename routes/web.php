<?php

use App\Http\Controllers\ApkVersionController;
use App\Http\Controllers\HelperController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/403', function () {
    return response()->json(['message' => 'Unauthenticated'], 401);
})->name('login');


Route::group(['prefix' => 'apk_versions'], function () {
    Route::get('/login', [ApkVersionController::class, 'loginPage'])->name('apk-version.login-page');
    Route::post('/login', [ApkVersionController::class, 'login'])->name('apk-version.login');
    Route::get('/logout', [ApkVersionController::class, 'logout'])->name('apk-version.logout');
    Route::get('/latest/{platform}/{channel}', [ApkVersionController::class, 'latest'])->name('apk-version.latest');
    Route::get('/download/{apk_version}', [ApkVersionController::class, 'download'])->name('apk-version.download');
    Route::get('/destroy/{apk_version}', [ApkVersionController::class, 'destroy'])->name('apk-version.destroy');
    Route::post('/store', [ApkVersionController::class, 'store'])->name('apk-version.store');
    Route::get('/', [ApkVersionController::class, 'index'])->name('apk-version.index');
});
