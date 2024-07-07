<?php

namespace App\Http\Controllers\Dir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

use MoveMoveIo\DaData\Enums\Language;
use MoveMoveIo\DaData\Enums\CompanyType;
use MoveMoveIo\DaData\Enums\CompanyStatus;
use MoveMoveIo\DaData\Facades\DaDataBank;
use MoveMoveIo\DaData\Facades\DaDataCompany;
use MoveMoveIo\DaData\Facades\DaDataAddress;

class SuggestController extends Controller
{
    public function getCargoNameSuggest(Request $request)
    {
        $res = Order::where("cargo_name", "ilike", "%{$request['q']}%")
            ->distinct()
            ->limit(25)
            ->get("cargo_name as value", "cargo_name as label")
            ->toArray();
        return response()->json($res);
    }

    public function fetchContactInfoByINN(Request $request)
    {
        $data = $request->validate([
            'inn' => 'required|digits_between:10,12'
        ]);

        if (strlen($data['inn']) === 11) {
            return response()->json(["code" => 400, "message" => "Invalid inn.", "errors" => ["inn" => ["Invalid inn."]]], 400);
        }
        $info = DaDataCompany::id($data['inn']);

        if (count($info["suggestions"]) == 0) {
            return response()->json(["code" => 404, "message" => "Not found.", "errors" => ["inn" => ["inn not found."]]], 400);
        }
        $d = $info["suggestions"][0]["data"];
        $type = $d["type"];

        $contacts =[];

        if (key_exists("address", $d) && $d["address"] != null) {
            $contacts[] = [
                "type" => "ADDRESS",
                "value" => $d["address"]["unrestricted_value"],
                "note" => $type == "LEGAL" ? "Юридический адрес" : "Адрес регистрации"
            ];
            if ($type == "LEGAL") {
                $contacts[] = [
                    "type" => "ADDRESS",
                    "value" => $d["address"]["unrestricted_value"],
                    "note" => "Фактический адрес"
                ];
            }
        }
        if ($type == "LEGAL" && key_exists("management", $d) && $d["management"] != null) {
            $contacts[] = [
                "type" => "PERSON",
                "value" => $d["management"]["name"],
                "note" => $d["management"]["post"]
            ];
        }

        if (key_exists("phones", $d) && $d["phones"] != null) {
            foreach ($d["phones"] as $phone) {
                $contacts[] = [
                    "type" => "PHONE",
                    "value" => $phone["value"],
                    "note" => $phone["data"]["type"]?:'',
                ];
            }
        }

        if (key_exists("emails", $d) && $d["emails"] != null) {
            foreach ($d["emails"] as $email) {
                $contacts[] = [
                    "type" => "PHONE",
                    "value" => $email["value"],
                    "note" => $email["data"]["type"]?:'',
                ];
            }
        }

        return [
            "name" => $d["name"]["short_with_opf"],
            "status" => $d["state"]["status"],
            "type" => $d["type"],
            "contacts" => $contacts,
        ];
    }

    public function firmSuggest(Request $request)
    {
        $data = $request->validate([
            "q" => "string|required",
        ]);
        $dataLegal = DaDataCompany::prompt(
            $data["q"],
            5,
            [CompanyStatus::ACTIVE],
            CompanyType::LEGAL
        );
        $dataIndividual = DaDataCompany::prompt(
            $data["q"],
            5,
            [CompanyStatus::ACTIVE]
        );

        return response()->json(array_map(fn($el) => [
            "name_short" => $el["data"]["name"]["short_with_opf"],
            "name_full" => $el["data"]["name"]["full_with_opf"],
            "type" => $el["data"]["type"],
            "inn" => $el["data"]["inn"],
            "kpp" => $el["data"]["type"] == "LEGAL" ? $el["data"]["kpp"] : null,
            "ogrn" => $el["data"]["ogrn"],
        ], [...$dataLegal["suggestions"], ...$dataIndividual["suggestions"]]));
    }

    public function bankSuggest(Request $request)
    {
        $data = $request->validate([
            "q" => "string|required",
        ]);

        $res = DaDataBank::id($data["q"]);

        return response()->json(array_map(fn($el) => [
            "bik" => $el["data"]["bic"],
            "bank_name" => $el["data"]["name"]["payment"],
            "payment_city" => $el["data"]["payment_city"],
            "account_correspondent" => $el["data"]["correspondent_account"],
        ], $res["suggestions"]));
    }

    public function addressSuggest(Request $request) {
        $data = $request->validate([
            "q" => "string|required",
        ]);

        $res = DaDataAddress::prompt($data["q"], 5, Language::RU);

        return response()->json(array_map(fn($el) => $el["value"], $res["suggestions"]));
    }
}
