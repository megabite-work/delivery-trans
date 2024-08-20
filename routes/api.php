<?php

use App\Http\Controllers\Auth\RoleController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Registry\RegistryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Dir\BankAccountController;
use App\Http\Controllers\Dir\CarBodyTypeController;
use App\Http\Controllers\Dir\ContactController;
use App\Http\Controllers\Dir\CountriesController;
use App\Http\Controllers\Dir\SuggestController;
use App\Http\Controllers\Dir\TonnageController;
use App\Http\Controllers\Dir\TConditionsController;
use App\Http\Controllers\Dir\CarCapacityController;
use App\Http\Controllers\Dir\AdditionalServiceController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Carrier\CarController;
use App\Http\Controllers\Carrier\DriverController;
use App\Http\Controllers\Carrier\CarrierController;
use App\Http\Controllers\Price\PriceController;
use App\Http\Controllers\Price\DefaultPriceController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\CalculationController;
use App\Http\Controllers\Registry\CarrierRegistryController;
use App\Http\Resources\UserResource;

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', function (Request $request) {
        return new UserResource($request->user());
    });
    Route::post('orders/{order}/status', [OrderController::class, 'setStatus'])->name('orders.status');
    Route::apiResource('carriers', CarrierController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('contacts', ContactController::class)->except(['index']);
    Route::apiResource('bank-accounts', BankAccountController::class)->except(['index']);
    Route::apiResource('cars', CarController::class)->except(['index']);
    Route::apiResource('drivers', DriverController::class)->except(['index']);
    Route::apiResource('additional-services', AdditionalServiceController::class);
    Route::apiResource('car_capacities', CarCapacityController::class);
    Route::apiResource('car-body-types', CarBodyTypeController::class)->except(['show', 'update']);
    Route::apiResource('tconditions', TConditionsController::class)->except(['show', 'update']);
    Route::apiResource('tonnages', TonnageController::class)->except(['show', 'update']);
    Route::apiResource('prices', PriceController::class)->except(['index', 'store']);
    Route::apiResource('additional-services', AdditionalServiceController::class)->except(['index', 'store']);
    Route::apiResource('default-prices', DefaultPriceController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('registries', RegistryController::class)->except(['index']);
    Route::apiResource('carrier-registries', CarrierRegistryController::class)->except(['index']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class);

    Route::get('clients/{client_id}/contacts', [ContactController::class, 'clientContactsIndex'])->name('client.contacts.index');
    Route::get('clients/{client_id}/bank-accounts', [BankAccountController::class, 'clientBankAccountsIndex'])->name('client.bank-accounts.index');

    Route::post('clients/{client_id}/price', [PriceController::class, 'storeForClient'])->name('client.price.store');
    Route::post('clients/{client_id}/additional-service', [AdditionalServiceController::class, 'storeForClient'])->name('client.price.store');
    Route::post('default-prices/{price_id}/price', [PriceController::class, 'storeForDefault'])->name('default.price.store');
    Route::post('default-prices/{price_id}/additional-service', [AdditionalServiceController::class, 'storeForDefault'])->name('default.price.additional-service.store');

    Route::get('carriers/{carrier_id}/contacts', [ContactController::class, 'carrierContactsIndex'])->name('carrier.contacts.index');
    Route::get('carriers/{carrier_id}/bank-accounts', [BankAccountController::class, 'carrierBankAccountsIndex'])->name('carrier.bank-accounts.index');
    Route::get('carriers/{carrier_id}/cars', [CarController::class, 'carrierCarsIndex'])->name('carrier.cars.index');
    Route::get('carriers/{carrier_id}/drivers', [DriverController::class, 'carrierDriversIndex'])->name('carrier.drivers.index');

    Route::get('suggest/car/body-types', [CarBodyTypeController::class, 'index'])->name("car.body-type.index");
    Route::get('suggest/countries', [CountriesController::class, 'index'])->name("countries.index");
    Route::get('suggest/cargo-name', [SuggestController::class, 'getCargoNameSuggest'])->name("suggest.cargo-name");
    Route::get('suggest/contacts-by-inn', [SuggestController::class, 'fetchContactInfoByINN']);
    Route::get('suggest/tonnages', [TonnageController::class, 'getTonnage'])->name("suggest.tonnage");
    Route::get('suggest/t-conditions', [TConditionsController::class, 'index'])->name("suggest.t-condition");
    Route::get('suggest/client-search', [ClientController::class, 'searchSuggest'])->name("suggest.client-search");
    Route::get('suggest/client-contact', [ContactController::class, 'clientContactSuggest'])->name("suggest.client-contact");
    Route::get('suggest/carrier-search', [CarrierController::class, 'searchSuggest'])->name("suggest.carrier-search");
    Route::get('suggest/drivers-by-carrier', [DriverController::class, 'getDriversByCarrierID'])->name("suggest.drivers-by-carrier");
    Route::get('suggest/cars-by-carrier', [CarController::class, 'getCarsByCarrierId'])->name('suggest.cars-by-carrier');
    Route::get('suggest/firms', [SuggestController::class, 'firmSuggest'])->name("suggest.firms");
    Route::get('suggest/bank', [SuggestController::class, 'bankSuggest'])->name("suggest.bank");
    Route::get('suggest/address', [SuggestController::class, 'addressSuggest'])->name("suggest.address");
    Route::get('suggest/additional-services/name', [AdditionalServiceController::class, 'nameSuggest'])->name("suggest.additional-services.name");
    Route::get('suggest/additional-services/price', [AdditionalServiceController::class, 'priceSuggest'])->name("suggest.additional-services.price");
    Route::get('suggest/order-driver-car', [OrderController::class, 'getLastOrderDriverCars'])->name("suggest.order-driver-car");
    Route::get('suggest/expenses', [OrderController::class, 'getAdditionalExpensesSuggestions'])->name("suggest.expenses");
    Route::get('suggest/roles', [RoleController::class, 'getRolesList'])->name("suggest.roles");
    Route::post('calculate', [CalculationController::class, 'calculate'])->name('orders.calculate');
});


