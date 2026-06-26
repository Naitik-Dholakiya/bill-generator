<?php

// use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

use App\Http\Controllers\AuthController;
use App\Http\Controllers\settingController;
use App\Http\Controllers\customerController;
use App\Http\Controllers\supplierController;

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

Route::get('/customers/dashboard', [customerController::class, 'index'])->name('customers.index');
Route::get('/customers/create', [customerController::class,'createCustomer'])->name('createCustomer');
Route::post('/customers/create', [customerController::class,'createCustomerPost'])->name('createCustomerPost');
Route::get('/customer/view/{id}', [CustomerController::class, 'viewCustomer'])->name('viewCustomer');

Route::get('/customer/edit/{id}', [CustomerController::class, 'editCustomer'])->name('editCustomer');

Route::put('/customer/edit/{id}', [CustomerController::class, 'editCustomerPost'])->name('editCustomerPost');

Route::delete('/customer/delete/{id}', [CustomerController::class, 'deleteCustomer'])->name('deleteCustomer');

// Route::delete('/customer/permanent-delete/{id}', [CustomerController::class, '#'])->name('permanentDeleteCustomer');

Route::get('/suppliers/dashboard', [supplierController::class, 'index'])->name('suppliers.index');