<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class CarrierResource extends JsonResource
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
        if ($request->user()->canDo("CARRIERS_REGISTRIES_VIEW")) {
            $query = <<<SQL
                select carrier_id, count(*) as count_with_docs, sum(c.carrier_sum) as sum_with_docs
                from (select distinct on (o.id) o.carrier_id as carrier_id, o.carrier_sum
                      from orders o
                               left join order_statuses os on os.order_id = o.id
                      where o.carrier_id = :carrier_id
                        and os.type = 'LOGIST'
                        and os.status = 'DOCUMENTS_SUBMITTED') c
                group by carrier_id
            SQL;

            $query = DB::select($query, ["carrier_id" => $this->id]);
            if (count($query)>0) {
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
            'is_resident' => $this->is_resident,
            'is_active' => $this->is_active,
            'contacts' => ContactResource::collection($this->contacts),
            'bank_accounts' => BankAccountResource::collection($this->bankAccounts),
            'cars' => CarResource::collection($this->cars),
            'drivers' => DriverResource::collection($this->drivers),
            'orders' => CarrierRegistryOrderResource::collection($this->orders),
            'registries' => CarrierRegistryResource::collection($this->registries),
            'statistics' => $query,
        ];
    }
}
