<?php

namespace App\Http\Controllers\Dir;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            "template_client" => "nullable|string",
            "template_carrier" => "nullable|string",
        ]);

        $company->update($data);
        return response()->json(new CompanyResource($company));
    }

    public function uploadTemplate(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        $file = $request->file('file');
        if ($file->isValid()) {
            $file_name = $file->getClientOriginalName();
            $file_name = Str::random(16).'.'.Str::of($file_name)->afterLast('.');
            $file->storeAs('', $file_name, 'templates');

            return response()->json([
                'name' => $file_name,
                'status' => 'done',
                'url' => "/storage/templates/{$file_name}",
            ], 201);
        }

        return response()->json([
            'status' => 'error'
        ], 400);
    }
}
