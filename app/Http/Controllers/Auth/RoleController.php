<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return RoleResource::collection(Role::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'permissions' => 'required|array'
        ]);
        $p = [];
        foreach ($data['permissions'] as $permission) {
            if (Permissions::tryFrom($permission) != null) {
                $p[] = $permission;
            }
        }
        $role = Role::create([
            'name' => $data['name'],
            'permissions' => $p,
            'created_by' => $request->user()->name,
            'updated_by' => $request->user()->name,
        ]);

        return response()->json(new RoleResource($role), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'permissions' => 'required|array'
        ]);
        $p = [];
        foreach ($data['permissions'] as $permission) {
            if (Permissions::tryFrom($permission) != null) {
                $p[] = $permission;
            }
        }
        $role->update([
            'name' => $data['name'],
            'permissions' => $p,
            'updated_by' => $request->user()->name,
        ]);

        return response()->json(new RoleResource($role));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->noContent();
    }
}
