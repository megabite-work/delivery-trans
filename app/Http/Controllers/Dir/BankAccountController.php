<?php

namespace App\Http\Controllers\Dir;

use App\Http\Controllers\Controller;
use App\Http\Resources\DTApiResource;
use App\Models\Client;
use App\Models\Carrier;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        return BankAccount::where('client_id', $request['client_id'])->get();
    }

    public function clientBankAccountsIndex(Request $request)
    {
        return BankAccount::whereHasMorph('owner', [Client::class],  function (Builder $query) use ($request) {
            $query->where('owner_id', $request['client_id']);
        })->get();
    }
    public function carrierBankAccountsIndex(Request $request)
    {
        return BankAccount::whereHasMorph('owner', [Carrier::class],  function (Builder $query) use ($request) {
            $query->where('owner_id', $request['carrier_id']);
        })->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_id' => 'required|morph_exists:owner_type',
            'owner_type' => 'required|string',
            'bik' => 'required|digits:9',
            'bank_name' => 'required|string',
            'payment_city' => 'required|string',
            'account_correspondent' => 'required|string',
            'account_payment' => 'required|string',
        ]);

        $bankAccount = BankAccount::create($data);
        return response()->json(new DTApiResource($bankAccount), 201);
    }

    public function show(BankAccount $bank_account)
    {
        return new DTApiResource($bank_account);
    }

    public function update(Request $request, BankAccount $bank_account)
    {
        $data = $request->validate([
            'bik' => 'required|digits:9',
            'bank_name' => 'required|string',
            'payment_city' => 'required|string',
            'account_correspondent' => 'required|string',
            'account_payment' => 'required|string',
        ]);

        $bank_account->update($data);
        return response()->json(new DTApiResource($bank_account));
    }

    public function destroy(BankAccount $bank_account)
    {
        $bank_account->delete();
        return response()->noContent();
    }
}
