<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::withCount('skinAnalyses');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        $users = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        $data = collect($users->items())->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'is_active' => $u->is_active,
            'is_admin' => $u->is_admin,
            'total_scans' => $u->skin_analyses_count,
            'created_at' => $u->created_at?->toIso8601String(),
        ]);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::withCount('skinAnalyses')->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
                'is_admin' => $user->is_admin,
                'device_id' => $user->device_id,
                'total_scans' => $user->skin_analyses_count,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', Password::defaults()],
                'phone' => ['nullable', 'string', 'max:20'],
                'is_active' => ['boolean'],
                'is_admin' => ['boolean'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'is_admin' => $validated['is_admin'] ?? false,
            ]);

            Log::info('Admin created user', [
                'admin_id' => $request->user()->id,
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'message' => 'User created successfully.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'is_active' => $user->is_active,
                    'is_admin' => $user->is_admin,
                ],
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
                'password' => ['sometimes', 'string', Password::defaults()],
                'phone' => ['nullable', 'string', 'max:20'],
                'is_active' => ['boolean'],
                'is_admin' => ['boolean'],
            ]);

            $data = collect($validated)->except('password')->toArray();
            if (! empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            Log::info('Admin updated user', [
                'admin_id' => $request->user()->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'message' => 'User updated successfully.',
                'data' => $user->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function toggleActive(int $id, Request $request): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);

        Log::info('Admin toggled user active status', [
            'admin_id' => $request->user()->id,
            'user_id' => $user->id,
            'is_active' => $user->fresh()->is_active,
        ]);

        return response()->json([
            'message' => $user->fresh()->is_active ? 'User activated.' : 'User deactivated.',
            'data' => ['is_active' => $user->fresh()->is_active],
        ]);
    }
}
