<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

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

Route::post('/payment/notify', [App\Http\Controllers\Order\CartController::class, 'handlePaymentNotification'])->name('payment.notify');
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
