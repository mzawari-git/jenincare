<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function getCartCount()
    {
        $cart = $this->getCart();
        $count = $cart->items->sum('quantity');

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['cart_count' => $count]);
        }

        return response()->json(['cart_count' => $count]);
    }

    public function index()
    {
        $cart = $this->getCart();
        $shippingCost = $this->calculateShippingCostForCart($cart);
        $freeShippingMin = $this->getFreeShippingMin();
        return view('frontend.cart.index', compact('cart', 'shippingCost', 'freeShippingMin'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = $this->getCart();

        $existingItem = $cart->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $request->quantity
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'unit_price' => $product->getCurrentPrice()
            ]);
        }

        $cart->load('items');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart',
                'cart_count' => $cart->items->sum('quantity')
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart');
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:0'
        ]);

        $cartItem = CartItem::findOrFail($request->cart_item_id);
        $cart = $this->getCart();

        if ($request->quantity == 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $request->quantity]);
        }

        $cart->load('items.product');
        $cartSubtotal = $cart->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $shippingCost = $this->calculateShippingCostForCart($cart);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item_total' => $request->quantity == 0 ? 0 : $cartItem->fresh()?->total ?? 0,
                'cart_subtotal' => number_format($cartSubtotal, 2, '.', ''),
                'cart_total' => number_format($cartSubtotal + $shippingCost, 2, '.', ''),
                'cart_count' => $cart->items->sum('quantity'),
                'shipping_cost' => number_format($shippingCost, 2, '.', ''),
            ]);
        }

        return redirect()->back();
    }

    public function remove(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id'
        ]);

        $cartItem = CartItem::findOrFail($request->cart_item_id);
        $cartItem->delete();

        $cart = $this->getCart();
        $cartSubtotal = $cart->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $shippingCost = $this->calculateShippingCostForCart($cart);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart_subtotal' => number_format($cartSubtotal, 2, '.', ''),
                'cart_total' => number_format($cartSubtotal + $shippingCost, 2, '.', ''),
                'cart_count' => $cart->items->sum('quantity'),
                'shipping_cost' => number_format($shippingCost, 2, '.', ''),
            ]);
        }

        return redirect()->back();
    }

    public function clear(Request $request)
    {
        $cart = $this->getCart();
        $cart->items()->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart_count' => 0
            ]);
        }

        return redirect()->route('cart')->with('success', 'تم تفريغ السلة');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'كود الخصم غير صالح أو منتهي الصلاحية'
            ]);
        }

        $cart = $this->getCart();
        $subtotal = $cart->items->sum(fn($item) => $item->quantity * $item->unit_price);

        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => 'الحد الأدنى للطلب هو ' . number_format($coupon->min_order_amount, 2) . ' ₪'
            ]);
        }

        $discount = $coupon->type === 'percentage'
            ? $subtotal * ($coupon->value / 100)
            : min($coupon->value, $subtotal);

        return response()->json([
            'success' => true,
            'message' => 'تم تطبيق كود الخصم بنجاح',
            'discount' => number_format($discount, 2, '.', '')
        ]);
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

    private function calculateShippingCostForCart($cart): float
    {
        $subtotal = $cart->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $freeShippingMin = $this->getFreeShippingMin();

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

    private function getFreeShippingMin(): float
    {
        $setting = Setting::where('key', 'free_shipping_min')->first();
        if (!$setting) {
            $setting = Setting::where('key', 'free_shipping_threshold')->first();
        }
        return $setting && is_numeric($setting->value) ? floatval($setting->value) : 200.00;
    }
}
