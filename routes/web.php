<?php

// use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

use App\Http\Controllers\AuthController;
use App\Http\Controllers\settingController;

Route::get("/secret/add-dummy-user", [AuthController::class, 'addDummyUser']);
Route::get('/secret/check-user', [AuthController::class, 'check']);

Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard')   ;

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/settings', [settingController::class, 'index'])->name('settings');
Route::post('/settings/changePassword', [settingController::class,'changePassword'])->name('changePassword');
