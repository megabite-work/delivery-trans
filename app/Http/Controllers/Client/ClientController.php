<?php

namespace App\Http\Controllers\Client;

use App\Http\Resources\DTApiCollection;
use App\Models\Client;
use App\Enums\ClientType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Enum;
use App\Http\Resources\ClientResource;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        return new DTApiCollection(Client::paginate($request['per_page']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_short' => 'required|string',
            'name_full' => 'required|string',
            'type' => [new Enum(ClientType::class)],
            'inn' => ClientType::tryFrom($request->type) == ClientType::LEGAL ?
                'required|digits:10|unique:App\Models\Client,inn':
                'required|digits:12|unique:App\Models\Client,inn',
            'kpp' => 'nullable|digits:9',
            'ogrn' => ClientType::tryFrom($request->type) == ClientType::LEGAL ?
                'nullable|digits:13':
                'nullable|digits:15',
        ]);

        $client = Client::create($data);
        return response()->json(new ClientResource($client), 201);
    }

    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name_short' => 'required|string',
            'name_full' => 'required|string',
            'type' => [new Enum(ClientType::class)],
            'inn' => ClientType::tryFrom($request->type) == ClientType::LEGAL ?
                'required|digits:10':
                'required|digits:12',
            'kpp' => 'nullable|digits:9',
            'ogrn' => ClientType::tryFrom($request->type) == ClientType::LEGAL ?
                'nullable|digits:13':
                'nullable|digits:15',
        ]);

        $client->update($data);
        return response()->json(new ClientResource($client));
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->noContent();
    }
}
