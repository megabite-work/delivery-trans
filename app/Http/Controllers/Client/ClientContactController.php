<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\ContactType;
use App\Models\ClientContact;
use App\Http\Controllers\Controller;
use App\Http\Resources\DTApiResource;

class ClientContactController extends Controller
{
    public function index(Request $request)
    {
        return ClientContact::where('client_id', $request['client_id'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:App\Models\Client,id',
            'type' => [Rule::enum(ContactType::class), 'required'],
            'value' => 'nullable',
            'note' => 'nullable',
        ]);

        $contact = ClientContact::create($data);
        return response()->json(new DTApiResource($contact), 201);
    }

    public function show(ClientContact $contact)
    {
        return new DTApiResource($contact);
    }

    public function update(Request $request, ClientContact $contact)
    {
        $data = $request->validate([
            'type' => [Rule::enum(ContactType::class), 'required'],
            'value' => 'nullable',
            'note' => 'nullable',
        ]);

        $contact->update($data);
        return response()->json(new DTApiResource($contact));
    }

    public function destroy(ClientContact $contact)
    {
        $contact->delete();
        return response()->noContent();
    }
}
