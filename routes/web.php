<?php
use App\Http\Controllers\AIDescriptionController;
use App\Http\Controllers\AdminCourierController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminWalletController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerOrderController;
use App\Http\Controllers\SellerProductController;
use App\Models\Account;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');

Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/items/{item}/confirm', [OrderController::class, 'confirmReceived'])->name('orders.items.confirm');
Route::post('/orders/items/{item}/complain', [OrderController::class, 'complain'])->name('orders.items.complain');

Route::prefix('/seller')->name('seller.')->group(function () {
    Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
    Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [SellerProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [SellerProductController::class, 'destroy'])->name('products.destroy');

    
    Route::post('/products/generate-ai', [AIDescriptionController::class, 'generate'])->name('products.generateAI');
    Route::post('/orders/{item}/process', [SellerOrderController::class, 'process'])->name('orders.process');
    Route::post('/orders/{item}/call-courier', [SellerOrderController::class, 'callCourier'])->name('orders.callCourier');
    Route::post('/orders/{item}/approve-return', [SellerOrderController::class, 'approveReturn'])->name('orders.approveReturn');
    Route::post('/orders/{item}/reject-complaint', [SellerOrderController::class, 'rejectComplaint'])->name('orders.rejectComplaint');
    Route::post('/orders/{item}/mark-failed', [SellerOrderController::class, 'markFailed'])->name('orders.markFailed');
});

Route::prefix('/courier')->name('courier.')->group(function () {
    Route::get('/', [CourierController::class, 'index'])->name('index');
    Route::post('/items/{item}/pickup', [CourierController::class, 'pickup'])->name('items.pickup');
    Route::post('/items/{item}/return-buyer', [CourierController::class, 'returnToBuyer'])->name('items.returnBuyer');
    Route::post('/items/{item}/delivered', [CourierController::class, 'delivered'])->name('items.delivered');
    Route::post('/items/{item}/return-seller', [CourierController::class, 'returnToSeller'])->name('items.returnSeller');
});

Route::prefix('/admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/couriers', [AdminCourierController::class, 'index'])->name('couriers.index');
    Route::get('/couriers/create', [AdminCourierController::class, 'create'])->name('couriers.create');
    Route::post('/couriers', [AdminCourierController::class, 'store'])->name('couriers.store');
    Route::get('/couriers/{courier}/edit', [AdminCourierController::class, 'edit'])->name('couriers.edit');
    Route::put('/couriers/{courier}', [AdminCourierController::class, 'update'])->name('couriers.update');
    Route::delete('/couriers/{courier}', [AdminCourierController::class, 'destroy'])->name('couriers.destroy');

    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::patch('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/wallets', [AdminWalletController::class, 'index'])->name('wallets.index');
    Route::patch('/wallets/{wallet}', [AdminWalletController::class, 'update'])->name('wallets.update');
});
