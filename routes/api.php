<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Client\ClientContactController;
use App\Http\Controllers\Client\ClientBankAccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::apiResources([
    'clients' => ClientController::class,
]);
Route::get('clients/{client_id}/contacts', [ClientContactController::class, 'index'])->name('client.contacts.index');
Route::get('clients/{client_id}/bank-accounts', [ClientBankAccountController::class, 'index'])->name('client.bank-accounts.index');

Route::apiResource('contacts', ClientContactController::class)->except(['index']);

Route::apiResource('bank-accounts', ClientBankAccountController::class)->except(['index']);
