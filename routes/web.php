<?php

use App\Http\Controllers\Dir\CompanyController;
use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\Route;

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
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload/order-template', [CompanyController::class, 'uploadTemplate']);
    Route::get('/download/client-registry/{registry}', [PDFController::class, 'getClientRegistry']);
    Route::get('/download/carrier-registry/{carrierRegistry}', [PDFController::class, 'getCarrierRegistry']);
});
Route::get('{any?}', function () {
    return view('main');
})->where('any', '.*');

require __DIR__.'/auth.php';
