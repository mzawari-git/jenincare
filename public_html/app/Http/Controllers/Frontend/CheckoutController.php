<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmation;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = $this->getCart();
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart');
        }

        $settings = Setting::pluck('value', 'key')->toArray();
        $paymentMethods = $this->getActivePaymentMethods($settings);
        $subtotal = $cart->items->sum(fn($item) => $item->unit_price * $item->quantity);
        $shippingCost = $this->calculateShippingCost($cart, $subtotal);
        $totalAmount = $subtotal + $shippingCost;

        return view('frontend.checkout.index', compact('cart', 'paymentMethods', 'settings', 'subtotal', 'shippingCost', 'totalAmount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'payment_method' => 'required|in:cod,bank_transfer,jawwal_pay,reflect,credit_card'
        ]);

        $cart = $this->getCart();
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart');
        }

        DB::beginTransaction();
        try {
            $subtotal = $cart->items->sum(fn($item) => $item->unit_price * $item->quantity);
            $shippingCost = $this->calculateShippingCost($cart, $subtotal);
            $totalAmount = $subtotal + $shippingCost;

            $order = Order::create([
                'tenant_id' => 1,
                'order_number' => Order::generateOrderNumber(),
                'order_type' => 'b2c',
                'user_id' => Auth::check() ? Auth::id() : null,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_region' => $request->shipping_region ?? null,
                'shipping_country' => 'PS',
                'shipping_notes' => $request->shipping_notes ?? null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'currency' => 'ILS',
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'ip_address' => $request->ip()
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_sku' => $item->product->sku,
                    'product_name' => $item->product->name_ar,
                    'product_image' => $item->product->main_image,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->unit_price * $item->quantity,
                    'total' => $item->unit_price * $item->quantity
                ]);

                $item->product->confirmSale($item->quantity);
            }

            $cart->items()->delete();

            DB::commit();

            try {
                Mail::to($request->customer_email)->send(new OrderConfirmation($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send order confirmation email: ' . $e->getMessage());
            }

            \App\Jobs\TrackPurchaseEvent::dispatch($order, [
                'email' => $request->customer_email,
                'phone' => $request->customer_phone,
                'name' => $request->customer_name,
            ]);

            return redirect()->route('checkout.success', $order->id);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create order. Please try again.');
        }
    }

    public function success($orderId)
    {
        $order = Order::findOrFail($orderId);
        return view('frontend.checkout.success', compact('order'));
    }

    private function getActivePaymentMethods(array $settings): array
    {
        $methods = [];

        // COD - enabled by default
        if (($settings['payment_cod_enabled'] ?? '1') == '1') {
            $methods['cod'] = [
                'id' => 'cod',
                'name' => 'الدفع عند الاستلام',
                'description' => 'ادفع نقداً عند استلام الطلب',
                'icon' => 'fa-money-bill-wave',
                'color' => '#10B981',
            ];
        }

        // Bank Transfer
        if (($settings['payment_bank_enabled'] ?? '0') == '1') {
            $methods['bank_transfer'] = [
                'id' => 'bank_transfer',
                'name' => 'التحويل البنكي',
                'description' => 'حوّل المبلغ إلى حسابنا البنكي',
                'icon' => 'fa-university',
                'color' => '#3B82F6',
            ];
        }

        // Jawwal Pay
        if (($settings['payment_jawwal_enabled'] ?? '0') == '1') {
            $methods['jawwal_pay'] = [
                'id' => 'jawwal_pay',
                'name' => 'جوال باي (Jawwal Pay)',
                'description' => 'ادفع عبر محفظة جوال باي',
                'icon' => 'fa-mobile-alt',
                'color' => '#F59E0B',
            ];
        }

        // Reflect
        if (($settings['payment_reflect_enabled'] ?? '0') == '1') {
            $methods['reflect'] = [
                'id' => 'reflect',
                'name' => 'ريفلكت (Reflect)',
                'description' => 'ادفع عبر تطبيق Reflect البنكي الرقمي',
                'icon' => 'fa-university',
                'color' => '#0891B2',
            ];
        }

        return $methods;
    }

    private function calculateShippingCost($cart, float $subtotal): float
    {
        $freeShippingMin = floatval(Setting::where('key', 'free_shipping_min')->first()?->value
            ?? Setting::where('key', 'free_shipping_threshold')->first()?->value
            ?? 200);

        if ($subtotal >= $freeShippingMin) {
            return 0;
        }

        foreach ($cart->items as $item) {
            if ($item->product && $item->product->free_shipping) {
                return 0;
            }
        }

        $shippingCostSetting = Setting::where('key', 'shipping_cost')->first();
        $defaultShipping = $shippingCostSetting && is_numeric($shippingCostSetting->value)
            ? floatval($shippingCostSetting->value)
            : 25.00;

        foreach ($cart->items as $item) {
            if ($item->product && $item->product->shipping_cost > 0) {
                return floatval($item->product->shipping_cost);
            }
        }

        return floatval($defaultShipping);
    }

    private function getCart()
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id(), 'is_active' => true],
                ['tenant_id' => 1]
            );
        } else {
            $sessionId = Session::getId();
            $cart = Cart::firstOrCreate(
                ['session_id' => $sessionId, 'is_active' => true],
                ['tenant_id' => 1]
            );
        }
        return $cart->load('items.product');
    }
}
