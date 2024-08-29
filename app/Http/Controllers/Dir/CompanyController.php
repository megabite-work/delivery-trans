<?php

namespace App\Http\Controllers\Dir;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        return response()->json(Company::all());
    }

    public function show(Company $company)
    {
        return new CompanyResource($company);
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            "name_short" => "required|string",
            "name_full" => "required|string",
            "inn" => "required|digits:10",
            "kpp" => "nullable|digits:9",
            "ogrn" => "nullable|digits:13",
            "bik" => "nullable|digits:9",
            "bank_name" => "nullable|string",
            "payment_city" => "nullable|string",
            "account_correspondent" => "nullable|string",
            "account_payment" => "nullable|string",
            "sign_position" => "nullable|string",
            "sign_name" => "nullable|string",
            "template" => "nullable|file|mimes:dotx",
        ]);

        if($request->has("template")) {
            $file = $request->file("template");
            if ($file->isValid()) {
                $fileName = $file->getClientOriginalName();
                $file->storeAs('', $fileName, 'templates');
                $data["template"] = $fileName;
            }
        }

        $company->update($data);
        return response()->json(new CompanyResource($company));
    }
}
