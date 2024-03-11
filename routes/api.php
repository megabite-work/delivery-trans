<?php

use App\Http\Controllers\Carrier\CarrierController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Dir\BankAccountController;
use App\Http\Controllers\Dir\CarBodyTypeController;
use App\Http\Controllers\Dir\ContactController;
use App\Http\Controllers\Dir\CountriesController;
use App\Http\Controllers\Carrier\CarController;
use App\Http\Controllers\Carrier\DriverController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('clients/{client_id}/contacts', [ContactController::class, 'clientContactsIndex'])->name('client.contacts.index');
Route::get('clients/{client_id}/bank-accounts', [BankAccountController::class, 'clientBankAccountsIndex'])->name('client.bank-accounts.index');

Route::apiResources([
    'carriers' => CarrierController::class,
]);
Route::get('carriers/{carrier_id}/contacts', [ContactController::class, 'carrierContactsIndex'])->name('carrier.contacts.index');
Route::get('carriers/{carrier_id}/bank-accounts', [BankAccountController::class, 'carrierBankAccountsIndex'])->name('carrier.bank-accounts.index');
Route::get('carriers/{carrier_id}/cars', [CarController::class, 'carrierCarsIndex'])->name('carrier.cars.index');
Route::get('carriers/{carrier_id}/drivers', [DriverController::class, 'carrierDriversIndex'])->name('carrier.drivers.index');

Route::get('suggest/car/body-types', [CarBodyTypeController::class, 'index'])->name("car.body-type.index");
Route::get('suggest/countries', [CountriesController::class, 'index'])->name("countries.index");

Route::apiResource('contacts', ContactController::class)->except(['index']);
Route::apiResource('bank-accounts', BankAccountController::class)->except(['index']);
Route::apiResource('cars', CarController::class)->except(['index']);
Route::apiResource('drivers', DriverController::class)->except(['index']);

