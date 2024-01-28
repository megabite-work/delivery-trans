<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Traits\DTApiCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClientCollection extends ResourceCollection
{
    use DTApiCollection;
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
         return parent::toArray($request);
    }
}
