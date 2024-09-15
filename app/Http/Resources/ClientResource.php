<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ClientResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $query = null;
        if ($request->user()->canDo("CLIENTS_REGISTRIES_VIEW")) {
            $query = <<<SQL
            select c.id as client_id,
                   wor.count_without_registry,
                   wor.sum_without_registry,
                   owd.count_with_doc,
                   owd.sum_with_doc,
                   npr.debt_with_bill
            from clients c
                     left join (select o.client_id       as client_id,
                                       count(*)          as count_without_registry,
                                       sum(o.client_sum) as sum_without_registry
                                from orders o
                                where client_id = :client_id
                                  and registry_id is null
                                group by o.client_id) wor on c.id = wor.client_id
                     left join (select t.client_id, count(*) as count_with_doc, sum(t.client_sum) as sum_with_doc
                                from (select distinct on (o.id) o.client_id as client_id, o.client_sum as client_sum
                                      from orders o
                                               left join order_statuses os on os.order_id = o.id
                                      where client_id = :client_id
                                        and registry_id is null
                                        and os.type = 'MANAGER'
                                        and os.status = 'DOCUMENTS_ACCEPTED') t
                                group by t.client_id) owd on c.id = owd.client_id
                     left join (select client_id, sum(client_sum) as debt_with_bill
                                from registries
                                where client_id = :client_id
                                  and client_sum > client_paid
                                  and (bill_number notnull or bill_date notnull)
                                group by client_id) npr on c.id = npr.client_id
            where c.id = :client_id
        SQL;

            $query = DB::select($query, ["client_id" => $this->id]);
            if (count($query) > 0) {
                $query = $query[0];
            }
        }

        return [
            'id' => $this->id,
            'name_short' => $this->name_short,
            'name_full' => $this->name_full,
            'type' => $this->type,
            'inn' => $this->inn,
            'kpp' => $this->kpp,
            'ogrn' => $this->ogrn,
            'ogrnip_date' => $this->ogrnip_date,
            'vat' => $this->vat,
            'contacts' => ContactResource::collection($this->contacts),
            'bank_accounts' => BankAccountResource::collection($this->bankAccounts),
            'prices' => PriceResource::collection($this->prices),
            "additional_services" => AdditionalServiceResource::collection($this->additionalServicesPrices),
            'orders' => ClientRegistryOrderResource::collection($this->orders),
            'registries' => ClientRegistryResource::collection($this->registries),
            'statistics' => $query,
        ];
    }
}
