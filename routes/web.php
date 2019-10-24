<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('eroc/payment/success', 'API\v1\Payment\PaymentController@setPaymentSuccess');
Route::get('eroc/payment/{$ref_no}/{$convenience_fee}/{$gateway_name}/{$transection_status}', 'API\v1\Payment\PaymentController@paymentForName');

