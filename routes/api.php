<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Dir\BankAccountController;
use App\Http\Controllers\Dir\CarBodyTypeController;
use App\Http\Controllers\Dir\ContactController;
use App\Http\Controllers\Dir\CountriesController;
use App\Http\Controllers\Dir\SuggestController;
use App\Http\Controllers\Dir\TonnageController;
use App\Http\Controllers\Dir\TConditionsController;
use App\Http\Controllers\Carrier\CarController;
use App\Http\Controllers\Carrier\DriverController;
use App\Http\Controllers\Carrier\CarrierController;
use App\Http\Controllers\Client\ClientController;

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
Route::get('suggest/cargo-name', [SuggestController::class, 'getCargoNameSuggest'])->name("suggest.cargo-name");
Route::get('suggest/tonnages', [TonnageController::class, 'getTonnage'])->name("suggest.tonnage");
Route::get('suggest/t-conditions', [TConditionsController::class, 'index'])->name("suggest.t-condition");
Route::get('suggest/client-search', [ClientController::class, 'searchSuggest'])->name("suggest.client-search");
Route::get('suggest/carrier-search', [CarrierController::class, 'searchSuggest'])->name("suggest.carrier-search");
Route::get('suggest/driver-by-carrier', [DriverController::class, 'getDriversByCarrierID'])->name("suggest.driver-by-carrier");
Route::apiResource('contacts', ContactController::class)->except(['index']);
Route::apiResource('bank-accounts', BankAccountController::class)->except(['index']);
Route::apiResource('cars', CarController::class)->except(['index']);
Route::apiResource('drivers', DriverController::class)->except(['index']);

