<?php

// use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/csrf-token', function () {
//     return response()->json(['token' => csrf_token()]);
// });

use App\Http\Controllers\AuthController;
use App\Http\Controllers\settingController;
use App\Http\Controllers\customerController;
use App\Http\Controllers\supplierController;
use App\Http\Controllers\productController;
use App\Http\Controllers\invoiceController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\invoiceGeneratorController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/', [AuthController::class, 'showLogin'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('admin.auth')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{userId}/edit', [AdminUserController::class, 'edit'])
            ->whereNumber('userId')
            ->name('users.edit');
        Route::put('/users/{userId}', [AdminUserController::class, 'update'])
            ->whereNumber('userId')
            ->name('users.update');
    });
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('user.auth')->group(function () {
    Route::get('/dashboard', [dashboardController::class, 'index'])->name('dashboard');

    Route::get('/settings', [settingController::class, 'index'])->name('settings');
    Route::put('/settings', [settingController::class, 'update'])->name('settings.update');
    Route::post('/settings/changePassword', [settingController::class,'changePassword'])->name('changePassword');

    Route::get('/customers/dashboard', [customerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [customerController::class,'createCustomer'])->name('createCustomer');
    Route::post('/customers/create', [customerController::class,'createCustomerPost'])->name('createCustomerPost');
    Route::get('/customer/view/{id}', [customerController::class, 'viewCustomer'])->name('viewCustomer');
    Route::get('/customer/edit/{id}', [customerController::class, 'editCustomer'])->name('editCustomer');
    Route::post('/customer/edit/{id}', [customerController::class, 'editCustomerPost'])->name('editCustomerPost');
    Route::delete('/customer/delete/{id}', [customerController::class, 'deleteCustomer'])->name('deleteCustomer');

    Route::get('/suppliers/dashboard', [supplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [supplierController::class,'createSupplier'])->name('createSupplier');
    Route::post('/suppliers/create', [supplierController::class,'createSupplierPost'])->name('createSupplierPost');
    Route::get('/supplier/view/{id}', [supplierController::class, 'viewSupplier'])->name('supplier.view');
    Route::get('/supplier/edit/{id}', [supplierController::class, 'editSupplier'])->name('supplier.edit');
    Route::put('/supplier/edit/{id}', [supplierController::class, 'editSupplierPost'])->name('editSupplierPost');
    Route::delete('/supplier/delete/{id}', [supplierController::class, 'deleteSupplier'])->name('supplier.delete');

    Route::get('/products/dashboard', [productController::class, 'index'])->name('products.index');
    Route::get('/products/create', [productController::class,'createProduct'])->name('products.create');
    Route::post('/products/create', [productController::class, 'createProductPost'])->name('createProductPost');
    Route::get('/product/view/{id}', [productController::class, 'viewProduct'])->name('products.view');
    Route::get('/product/edit/{id}', [productController::class, 'editProduct'])->name('products.edit');
    Route::put('/product/edit/{id}', [productController::class, 'editProductPost'])->name('editProductPost');
    Route::delete('/product/delete/{id}', [productController::class, 'deleteProduct'])->name('products.delete');
    Route::post('/category/ajax/store', [productController::class, 'storeAjaxCategory'])->name('category.store.ajax');

    Route::get('/invoice/dashboard', [invoiceController::class, 'index'])->name('invoice.index');
    Route::get('/invoice/create', [invoiceController::class, 'create'])->name('invoice.create');
    Route::get('/invoice/{invoice}', [invoiceController::class, 'view'])->name('invoice.view');
    Route::get('/invoice/{invoice}/edit', [invoiceController::class, 'edit'])->name('invoice.edit');
    Route::put('/invoice/{invoice}', [invoiceController::class, 'update'])->name('invoice.update');
    Route::delete('/invoice/{invoice}', [invoiceController::class, 'delete'])->name('invoice.delete');
    Route::post('/invoice/generate-pdf', [invoiceController::class, 'createInvoicePost'])->name('invoice.createInvoicePost');
    Route::get('/invoices/pdf/{invoice}', [invoiceGeneratorController::class, 'generate'])->name('invoice.pdf');
});

Route::get('/deploy-clear-cache', function (Request $request) {
    $expected = env('DEPLOY_TOKEN');
    $given = $request->header('X-Deploy-Token', $request->query('token', ''));

    abort_unless($expected && hash_equals($expected, $given), 404);

    Artisan::call('optimize:clear'); // config, route, view, cache, events, compiled
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }

    return response()->json(['message' => 'Cache cleared successfully.']);
});

Route::get('/add-admin-user', [AdminUserController::class, 'addAdminUser'])->name('add.admin.user');