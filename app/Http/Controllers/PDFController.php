<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\CarrierRegistry;
use App\Models\Company;
use App\Models\Registry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function getClientRegistry(Request $request, Registry  $registry)
    {
        if (!$request->user()->canDo('CLIENTS_REGISTRIES_DOWNLOAD')) {
            return response()->json(['message' => 'You do not have permission to access this page.'], 403);
        }
        $company = Company::where("vat", $registry->vat > 1 ? 0 : $registry->vat)->first();
        $data = [
            'title' => 'Реестр № '.$registry->id.' от '.$registry->created_at,
            'registry' => $registry,
            'orders' => OrderResource::collection($registry->orders),
            'company' => $company,
        ];
//        $pdf = PDF::loadView('pdf.clientRegistry', $data)->setPaper('A4', 'landscape');
//        return $pdf->download('client_registry_'.$registry->id.'_'.$registry->created_at->format("d.m.Y").'.pdf');
        return view('pdf.clientRegistry', $data);
    }

    public function getCarrierRegistry(Request $request, CarrierRegistry $carrierRegistry)
    {
        if (!$request->user()->canDo('CARRIERS_REGISTRIES_DOWNLOAD')) {
            return response()->json(['message' => 'You do not have permission to access this page.'], 403);
        }
        $company = Company::where("vat", $carrierRegistry->vat > 1 ? 0 : $carrierRegistry->vat)->first();
        $data = [
            'title' => 'Реестр № '.$carrierRegistry->id.' от '.$carrierRegistry->created_at,
            'registry' => $carrierRegistry,
            'orders' => OrderResource::collection($carrierRegistry->orders),
            'company' => $company,
        ];
//        $pdf = PDF::loadView('pdf.clientRegistry', $data)->setPaper('A4', 'landscape');
//        return $pdf->download('client_registry_'.$registry->id.'_'.$registry->created_at->format("d.m.Y").'.pdf');
        return view('pdf.carrierRegistry', $data);
    }
}
