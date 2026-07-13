<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $appSettings = AppSetting::first();
        if ($appSettings && ! $appSettings->login_enabled) {
            return response()->json([
                'message' => 'تسجيل الدخول معطل حالياً. يرجى المحاولة لاحقاً.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $credentials = $request->validate([
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
                'device_id' => ['nullable', 'string', 'max:255'],
            ]);

            if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
                return response()->json([
                    'message' => 'بيانات الدخول غير صحيحة',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = User::where('email', $credentials['email'])->first();

            if (! $user) {
                return response()->json([
                    'message' => 'المستخدم غير موجود',
                ], Response::HTTP_UNAUTHORIZED);
            }

            if (! $user->is_active) {
                Auth::logout();
                return response()->json([
                    'message' => 'تم تعطيل هذا الحساب. يرجى التواصل مع الإدارة.',
                ], Response::HTTP_FORBIDDEN);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            if (! empty($credentials['device_id'])) {
                $user->update(['device_id' => $credentials['device_id']]);
            }

            Log::info('User logged in', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'data' => [
                    'token' => $token,
                    'user' => $this->formatUser($user),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'بيانات غير صالحة',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function register(Request $request): JsonResponse
    {
        $appSettings = AppSetting::first();
        if ($appSettings && ! $appSettings->registration_enabled) {
            return response()->json([
                'message' => 'التسجيل معطل حالياً. يرجى المحاولة لاحقاً.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'confirmed', Password::defaults()],
                'phone' => ['nullable', 'string', 'max:20'],
                'device_id' => ['nullable', 'string', 'max:255'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'phone' => $validated['phone'] ?? null,
                'device_id' => $validated['device_id'] ?? null,
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            Log::info('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'data' => [
                    'token' => $token,
                    'user' => $this->formatUser($user),
                ],
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'بيانات غير صالحة',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $this->formatUser($user),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'current_password' => ['nullable', 'string', 'required_with:new_password'],
                'new_password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            ]);

            if (! empty($validated['new_password'])) {
                if (! Hash::check($validated['current_password'], $user->password)) {
                    return response()->json([
                        'message' => 'كلمة المرور الحالية غير صحيحة',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $user->password = $validated['new_password'];
            }

            $user->fill(array_filter([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]));

            if ($user->isDirty()) {
                $user->save();
            }

            Log::info('User profile updated', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'data' => $this->formatUser($user->fresh()),
                'message' => 'تم تحديث الملف الشخصي بنجاح',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'بيانات غير صالحة',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->user()->currentAccessToken()->delete();

        Log::info('User logged out', [
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }

    public function deviceRegister(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => ['required', 'string', 'max:255'],
            ]);

            $user = $request->user();

            $user->update(['device_id' => $validated['device_id']]);

            Log::info('Device registered', [
                'user_id' => $user->id,
                'device_id' => $validated['device_id'],
            ]);

            return response()->json([
                'message' => 'تم تسجيل الجهاز بنجاح',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'بيانات غير صالحة',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'created_at' => $user->created_at?->toIso8601String(),
            'has_pending_analysis' => $user->hasPendingAnalysis(),
            'total_analyses' => $user->skinAnalyses()->count(),
        ];
    }
}
