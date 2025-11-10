<?php

namespace App\Presentation\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController
{
    public function index(Request $request)
    {
        return User::query()
            ->when($request->query('role'), fn($q, $role) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate(20);
    }

    public function show(User $user)
    {
        return $user;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'whatsapp' => ['required', 'string', 'max:32'],
            'passport_no' => ['nullable', 'string', 'max:64', 'unique:users,passport_no'],
            'role' => ['required', 'in:staff,super_admin'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'whatsapp' => $data['whatsapp'],
            'passport_no' => $data['passport_no'] ?? uniqid('STAFF-'),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        if ($user->role === 'super_admin' && $request->user()->id !== $user->id) {
            return response()->json(['message' => 'Cannot modify super admin'], 422);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'email' => ['sometimes', 'email:rfc,dns', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'string', 'min:8'],
            'whatsapp' => ['sometimes', 'string', 'max:32'],
            'passport_no' => ['nullable', 'string', 'max:64', 'unique:users,passport_no,' . $user->id],
            'role' => ['sometimes', 'in:staff,super_admin,student'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        if ($user->role === 'super_admin') {
            return response()->json(['message' => 'Cannot delete super admin'], 422);
        }
        $user->delete();
        return response()->json(['ok' => true]);
    }
}
