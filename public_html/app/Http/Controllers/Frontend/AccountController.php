<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserAddress;
use App\Models\Wishlist;
use App\Models\Affiliate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $affiliate = Affiliate::where('user_id', Auth::id())->first();

        return view('frontend.account.index', compact('orders', 'affiliate'));
    }

    public function orders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('frontend.account.orders', compact('orders'));
    }

    public function orderShow($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('items')
            ->firstOrFail();

        return view('frontend.account.order-show', compact('order'));
    }

    public function addresses()
    {
        $addresses = Auth::user()->addresses;
        return view('frontend.account.addresses', compact('addresses'));
    }

    public function wishlist()
    {
        $wishlists = Auth::user()->wishlists()->with('product')->get();
        return view('frontend.account.wishlist', compact('wishlists'));
    }

    public function addToWishlist(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $exists = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->exists();
        if ($exists) {
            return response()->json(['success' => true, 'message' => 'المنتج موجود بالفعل في المفضلة']);
        }
        Wishlist::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);
        return response()->json(['success' => true]);
    }

    public function toggleWishlist(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'يرجى تسجيل الدخول'], 401);
        }
        $productId = $request->product_id;
        $existing = Wishlist::where('user_id', Auth::id())->where('product_id', $productId)->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['success' => true, 'action' => 'removed', 'message' => 'تمت الإزالة من المفضلة']);
        }
        Wishlist::create(['user_id' => Auth::id(), 'product_id' => $productId]);
        return response()->json(['success' => true, 'action' => 'added', 'message' => 'تمت الإضافة للمفضلة']);
    }
}
