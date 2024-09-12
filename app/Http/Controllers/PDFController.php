<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\CarrierRegistry;
use App\Models\Company;
use App\Models\Registry;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function getClientRegistry(Request $request, Registry $registry)
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

        $html = view('pdf.clientRegistry', $data)->render();
        $pdf = PDF::loadHTML($html, 'UTF-8')
            ->setPaper('A4', 'landscape');

        return $pdf->download('Реестр_клиента_'.$registry->id.'_'.$registry->client->name_short.'_'.$registry->created_at->format("d.m.Y").'.pdf');
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
        $pdf = PDF::loadView('pdf.carrierRegistry', $data)->setPaper('A4', 'landscape');
        return $pdf->download('Реестр_перевозчика_'.$carrierRegistry->id.'_'.$carrierRegistry->carrier->name_short.'_'.$carrierRegistry->created_at->format("d.m.Y").'.pdf');
    }
}
