<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Models\ClientBankAccount;
use App\Http\Controllers\Controller;
use App\Http\Resources\DTApiResource;

class ClientBankAccountController extends Controller
{
    public function index(Request $request)
    {
        return ClientBankAccount::where('client_id', $request['client_id'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:App\Models\Client,id',
            'bik' => 'required|digits:9',
            'bank_name' => 'required|string',
            'bank_name_payment' => 'required|string',
            'payment_city' => 'required|string',
            'account_correspondent' => 'required|string',
            'account_payment' => 'required|string',
        ]);

        $bankAccount = ClientBankAccount::create($data);
        return response()->json(new DTApiResource($bankAccount), 201);
    }

    public function show(ClientBankAccount $bank_account)
    {
        return new DTApiResource($bank_account);
    }

    public function update(Request $request, ClientBankAccount $bank_account)
    {
        $data = $request->validate([
            'bik' => 'required|digits:9',
            'bank_name' => 'required|string',
            'bank_name_payment' => 'required|string',
            'payment_city' => 'required|string',
            'account_correspondent' => 'required|string',
            'account_payment' => 'required|string',
        ]);

        $bank_account->update($data);
        return response()->json(new DTApiResource($bank_account));
    }

    public function destroy(ClientBankAccount $bank_account)
    {
        $bank_account->delete();
        return response()->noContent();
    }
}
