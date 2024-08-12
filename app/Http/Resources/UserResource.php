<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public static $wrap = false;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roles = RoleResource::collection($this->roles)->collection;
        if($this->is_superuser){
            $roles->prepend([
                'id' => 0,
                'name' => 'Cуперадмин',
                'permissions' => ['ALL'],
            ]);
        }

        return [
            "id" => $this->id,
            "email" => $this->email,
            "name" => $this->name,
            "roles" => $roles,
        ];
    }
}
