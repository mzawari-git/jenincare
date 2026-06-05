<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpinCode;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpinCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = SpinCode::latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                  ->orWhere('customer_email', 'like', "%{$request->search}%");
            });
        }

        if ($request->used !== null) {
            $query->where('is_used', filter_var($request->used, FILTER_VALIDATE_BOOLEAN));
        }

        $codes = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $codes->items(),
            'meta' => [
                'current_page' => $codes->currentPage(),
                'last_page' => $codes->lastPage(),
                'total' => $codes->total(),
                'per_page' => $codes->perPage(),
            ]
        ]);
    }

    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'nullable|email',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $code = SpinCode::create([
            'order_id' => $request->order_id,
            'customer_email' => $request->email,
            'code' => SpinCode::generateUniqueCode(),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء كود الدولب بنجاح',
            'data' => $code
        ]);
    }

    public function generateForOrder($orderId)
    {
        $order = Order::findOrFail($orderId);

        $existing = SpinCode::where('order_id', $order->id)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'data' => $existing
            ]);
        }

        $code = SpinCode::create([
            'order_id' => $order->id,
            'customer_email' => $order->customer_email,
            'code' => SpinCode::generateUniqueCode(),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'success' => true,
            'data' => $code
        ]);
    }

    public function validateCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $spinCode = SpinCode::where('code', $request->code)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (!$spinCode) {
            return response()->json([
                'success' => false,
                'message' => 'كود غير صالح أو منتهي الصلاحية'
            ]);
        }

        if ($spinCode->is_used) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الكود مستخدم بالفعل'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'كود صالح',
            'data' => $spinCode
        ]);
    }

    public function markUsed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20',
            'gift' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $spinCode = SpinCode::where('code', $request->code)
            ->where('is_used', false)
            ->first();

        if (!$spinCode) {
            return response()->json([
                'success' => false,
                'message' => 'كود غير صالح أو مستخدم بالفعل'
            ]);
        }

        $spinCode->update([
            'is_used' => true,
            'used_at' => now(),
            'gift' => $request->gift,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم استخدام الكود بنجاح',
            'data' => $spinCode
        ]);
    }

    public function show($id)
    {
        $code = SpinCode::with('order')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $code]);
    }
}
