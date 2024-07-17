<?php

namespace App\Http\Controllers\Dir;

use App\Enums\ContactType;
use App\Http\Controllers\Controller;
use App\Http\Resources\DTApiResource;
use App\Models\Carrier;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function clientContactSuggest(Request $request)
    {
        $data = $request->validate([
            "type" => [Rule::enum(ContactType::class), 'required'],
            "client_id" => 'required|exists:clients,id',
        ]);
        $data["q"] = $request->get("q", "");
        $query = <<<SQL
            select distinct value, note from contacts
                               where value ilike :q
                                 and type = :type
                                 and owner_type = :owner_type
                                 and owner_id = :owner_id
        SQL;
        $res = DB::select($query, [
            "q" => "%".$data["q"]."%",
            "type" => $data["type"],
            "owner_type" => Client::class,
            "owner_id" => $data["client_id"],
        ]);
        return response()->json($res);
    }
    public function clientContactsIndex(Request $request)
    {
        return Contact::whereHasMorph('owner', [Client::class],  function (Builder $query) use ($request) {
            $query->where('owner_id', $request['client_id']);
        })->get();
    }
    public function carrierContactsIndex(Request $request)
    {
        return Contact::whereHasMorph('owner', [Carrier::class],  function (Builder $query) use ($request) {
            $query->where('owner_id', $request['carrier_id']);
        })->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_id' => 'required|morph_exists:owner_type',
            'owner_type' => 'required|string',
            'type' => [Rule::enum(ContactType::class), 'required'],
            'value' => 'nullable',
            'note' => 'nullable',
        ]);

        $contact = Contact::create($data);
        return response()->json(new DTApiResource($contact), 201);
    }

    public function show(Contact $contact)
    {
        return new DTApiResource($contact);
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'type' => [Rule::enum(ContactType::class), 'required'],
            'value' => 'nullable',
            'note' => 'nullable',
        ]);

        $contact->update($data);
        return response()->json(new DTApiResource($contact));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->noContent();
    }


}
