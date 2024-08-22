<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function getCurrentUser(Request $request)
    {
        return new UserResource($request->user());
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return UserResource::collection(User::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'roles' => ['array'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if($request->has('roles')) {
            $rid = [];
            foreach ($request->get('roles', []) as $role) {
                $rid[] = $role['id'];
            }
            $user->roles()->sync($rid);
        }

        return response()->json(new UserResource($user), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => [Rules\Password::defaults()],
            'roles' => ['array'],
        ]);

        $user->update([
            "name" => $request->name,
            "email" => $request->email,
        ]);

        if ($request->has("password")) {
            $user->forceFill([
                'password' => Hash::make($request->password)
            ])->save();
        }

        if($request->has('roles')) {
            $rid = [];
            foreach ($request->get('roles', []) as $role) {
                if($role['id'] > 0){
                    $rid[] = $role['id'];
                }
            }
            $user->roles()->sync($rid);
        }

        return response()->json(new UserResource($user));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->email == $request->user()->email) {
            return response()->json(
                [
                    "code" => 400,
                    "message" => "Пользователь не может удалить себя"
                ], 400);
        }
        if ($user->is_superuser) {
            return response()->json(
                [
                    "code" => 400,
                    "message" => "Нельзя удалить этого пользователя"
                ], 400);
        }
        $user->delete();
        return response()->noContent();
    }
}
