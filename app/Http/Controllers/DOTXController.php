<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderTemplateResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Http\Resources\OrderResource;
use App\Models\Company;
use App\Models\Order;
use PhpOffice\PhpWord\TemplateProcessor;


class DOTXController extends Controller
{
    private function getOrderTmp(Request $request, Order $order, Company $company)
    {
        $vat = ["Без НДС", "C НДС", "Нал"];

        $order_tmp = (new OrderTemplateResource($order))->toArray($request);

        $order_tmp['company_name_short'] = $company->name_short;
        $order_tmp['company_name_full'] = $company->name_full;
        $order_tmp['company_inn'] = $company->inn;
        $order_tmp['company_kpp'] = $company->kpp;
        $order_tmp['company_ogrn'] = $company->ogrn;
        $order_tmp['company_vat'] = $vat[$company->vat];
        $order_tmp['company_bank'] = "р/с ".$company->account_payment.' в '.$company->bank_name.' '.$company->payment_city.', к/с '.$company->account_correspondent.', БИК '.$company->bik;
        $order_tmp['company_bank_bik'] = $company->bik;
        $order_tmp['company_bank_name'] = $company->bank_name;
        $order_tmp['company_bank_city'] = $company->payment_city;
        $order_tmp['company_bank_correspondent'] = $company->account_correspondent;
        $order_tmp['company_bank_account'] = $company->account_payment;
        $order_tmp['company_sign_position'] = $company->sign_position;
        $order_tmp['company_sign_name'] = $company->sign_name;

        return $order_tmp;
    }

    public function getClientOrder(Request $request, Order $order)
    {
        if (!$request->user()->canDo('CLIENTS_ORDERS_DOWNLOAD')) {
            return response()->json(['message' => 'You do not have permission to access this page.'], 403);
        }
        $company = Company::where("vat", $order->client_vat > 1 ? 0 : $order->client_vat)->first();

        $order_tmp = $this->getOrderTmp($request, $order, $company);
        $template_file = storage_path('app/templates/'.$company->template_client);
        $template_processor = new TemplateProcessor($template_file);
        $template_processor->setValues($order_tmp);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($template_processor) {
            $template_processor->saveAs('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.ms-word');
        $response->headers->set('Cache-Control', 'max-age=0');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'Order_'.$order->id.'_client.docx'
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    public function getCarrierOrder(Request $request, Order $order)
    {
        if (!$request->user()->canDo('CARRIERS_ORDERS_DOWNLOAD')) {
            return response()->json(['message' => 'You do not have permission to access this page.'], 403);
        }

        $company = Company::where("vat", $order->carrier_vat > 1 ? 0 : $order->carrier_vat)->first();

        $order_tmp = $this->getOrderTmp($request, $order, $company);

        $template_file = storage_path('app/templates/'.$company->template_carrier);
        $template_processor = new TemplateProcessor($template_file);
        $template_processor->setValues($order_tmp);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($template_processor) {
            $template_processor->saveAs('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.ms-word');
        $response->headers->set('Cache-Control', 'max-age=0');
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'Order_'.$order->id.'_carrier.docx'
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }


}
