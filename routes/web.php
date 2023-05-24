<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;

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

Route::post('user/register', [UserController::class, 'registerUserWithPassword']);

Route::post('login/auth/password', [LoginController::class, 'loginWithPassword']);

Route::get('login/auth/{provider}', [LoginController::class, 'redirectToProvider']);

Route::post('login/auth/{provider}/callback', [LoginController::class, 'handleProviderCallback']);

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
