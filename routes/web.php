<?php

use App\Http\Controllers\ProfileController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('login/okta', [App\Http\Controllers\Auth\LoginController::class, 'redirectToProvider'])->name('login-okta');
//Route::get('login/okta', 'Auth\LoginController@redirectToProvider')->name('login-okta');

Route::get('login/okta/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleProviderCallback']);
//Route::get('login/okta/callback', 'Auth\LoginController@handleProviderCallback');

Route::get('/personal-home', 'HomeController@personal')->name('personal-home')->middleware('auth');

Route::post('sso_logout', [App\Http\Controllers\Auth\LoginController::class, 'sso_logout'])->name('sso_logout');


//Route::post('ajaxRequest', [AjaxController::class, 'ajaxRequestPost'])->name('ajaxRequest.post');
Route::post('add_user', [App\Http\Controllers\Auth\LoginController::class, 'add_user'])->name('add_user');

Route::post('current_user', [App\Http\Controllers\Auth\LoginController::class, 'current_user'])->name('current_user');


