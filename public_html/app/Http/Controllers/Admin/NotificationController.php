<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orWhereNull('user_id')
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = Notification::whereNull('read_at')
            ->where(function ($q) {
                $q->where('user_id', Auth::id())->orWhereNull('user_id');
            })
            ->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function unread()
    {
        $notifications = Notification::whereNull('read_at')
            ->where(function ($q) {
                $q->where('user_id', Auth::id())->orWhereNull('user_id');
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'count' => $notifications->count(),
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::whereNull('read_at')
            ->where(function ($q) {
                $q->where('user_id', Auth::id())->orWhereNull('user_id');
            })
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    public static function notifyNewOrder(Order $order)
    {
        Notification::create([
            'type' => 'order',
            'title' => 'طلب جديد',
            'body' => "طلب جديد #{$order->order_number} بقيمة " . number_format($order->total_amount, 2) . " ₪",
            'data' => ['order_id' => $order->id, 'url' => route('admin.orders.show', $order)],
            'user_id' => null,
        ]);
    }

    public static function notifyLowStock($product)
    {
        Notification::create([
            'type' => 'inventory',
            'title' => 'تنبيه المخزون',
            'body' => "المنتج {$product->name_ar} وصل للحد الأدنى ({$product->stock_quantity} وحدة)",
            'data' => ['product_id' => $product->id, 'url' => route('admin.products.edit', $product)],
            'user_id' => null,
        ]);
    }
}
