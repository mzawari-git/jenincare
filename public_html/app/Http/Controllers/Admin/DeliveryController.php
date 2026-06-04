<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['order.user', 'order.items'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->driver, fn($q) => $q->where('driver_name', 'like', "%{$request->driver}%"))
            ->when($request->city, fn($q) => $q->where('delivery_city', 'like', "%{$request->city}%"))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->search, fn($q) => $q->where(function($sub) use ($request) {
                $sub->where('delivery_number', 'like', "%{$request->search}%")
                   ->orWhere('tracking_number', 'like', "%{$request->search}%")
                   ->orWhere('recipient_name', 'like', "%{$request->search}%")
                   ->orWhere('delivery_address', 'like', "%{$request->search}%");
            }));

        $deliveries = $query->latest()->paginate(20)->appends($request->all());

        $stats = [
            'total' => Delivery::count(),
            'pending' => Delivery::where('status', 'pending')->count(),
            'inProgress' => Delivery::whereIn('status', ['assigned', 'picked_up', 'in_transit', 'out_for_delivery'])->count(),
            'delivered' => Delivery::where('status', 'delivered')->count(),
            'failed' => Delivery::whereIn('status', ['failed', 'attempted', 'returned'])->count(),
        ];

        $drivers = Delivery::distinct()->whereNotNull('driver_name')->pluck('driver_name');

        return view('admin.deliveries.index', compact('deliveries', 'stats', 'drivers'));
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['order.user', 'order.items.product']);
        return view('admin.deliveries.show', compact('delivery'));
    }

    public function create(Request $request)
    {
        $order = null;
        if ($request->order_id) {
            $order = Order::with('items.product')->find($request->order_id);
        }

        $pendingOrders = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->whereDoesntHave('delivery')
            ->with('user')
            ->latest()
            ->limit(100)
            ->get();

        return view('admin.deliveries.create', compact('order', 'pendingOrders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id|unique:deliveries,order_id',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_vehicle' => 'nullable|string|max:100',
            'courier_service' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
            'delivery_address' => 'required|string',
            'delivery_city' => 'nullable|string|max:100',
            'delivery_region' => 'nullable|string|max:100',
            'delivery_cost' => 'nullable|numeric|min:0',
            'delivery_zone' => 'nullable|string|max:100',
            'estimated_delivery_days' => 'nullable|integer|min:1',
            'recipient_name' => 'nullable|string|max:255',
            'cod_amount' => 'nullable|numeric|min:0',
            'delivery_notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'status' => 'required|in:pending,assigned,picked_up,in_transit,out_for_delivery',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $validated['tenant_id'] = 1;
        $validated['delivery_number'] = Delivery::generateDeliveryNumber();
        $validated['delivery_address'] = $validated['delivery_address'] ?? $order->shipping_address;
        $validated['delivery_city'] = $validated['delivery_city'] ?? $order->shipping_city;
        $validated['delivery_region'] = $validated['delivery_region'] ?? $order->shipping_region;
        $validated['delivery_cost'] = $validated['delivery_cost'] ?? $order->shipping_cost;
        $validated['status_history'] = json_encode([[
            'status' => $validated['status'],
            'timestamp' => now()->toDateTimeString(),
            'notes' => 'تم إنشاء عملية التوصيل',
        ]]);

        if (in_array($validated['status'], ['assigned', 'picked_up'])) {
            $validated['assigned_at'] = now();
        }

        Delivery::create($validated);

        return redirect()->route('admin.deliveries.index')
            ->with('success', 'تم إنشاء عملية التوصيل بنجاح.');
    }

    public function edit(Delivery $delivery)
    {
        $delivery->load('order.user');
        return view('admin.deliveries.edit', compact('delivery'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_vehicle' => 'nullable|string|max:100',
            'courier_service' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'tracking_url' => 'nullable|url|max:500',
            'delivery_address' => 'nullable|string',
            'delivery_city' => 'nullable|string|max:100',
            'delivery_region' => 'nullable|string|max:100',
            'delivery_cost' => 'nullable|numeric|min:0',
            'delivery_zone' => 'nullable|string|max:100',
            'estimated_delivery_days' => 'nullable|integer|min:1',
            'recipient_name' => 'nullable|string|max:255',
            'cod_amount' => 'nullable|numeric|min:0',
            'cod_status' => 'nullable|in:pending,collected,settled,failed',
            'delivery_notes' => 'nullable|string',
            'internal_notes' => 'nullable|string',
        ]);

        $delivery->update($validated);

        return redirect()->route('admin.deliveries.index')
            ->with('success', 'تم تحديث بيانات التوصيل بنجاح.');
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,picked_up,in_transit,out_for_delivery,delivered,attempted,failed,returned,cancelled',
            'notes' => 'nullable|string',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_relation' => 'nullable|string|max:50',
        ]);

        $oldStatus = $delivery->status;
        $delivery->status = $validated['status'];

        if ($validated['recipient_name']) {
            $delivery->recipient_name = $validated['recipient_name'];
        }
        if ($validated['recipient_relation']) {
            $delivery->recipient_relation = $validated['recipient_relation'];
        }

        switch ($validated['status']) {
            case 'assigned':
                $delivery->assigned_at = now();
                break;
            case 'picked_up':
                $delivery->picked_up_at = now();
                break;
            case 'in_transit':
                $delivery->in_transit_at = now();
                break;
            case 'delivered':
                $delivery->delivered_at = now();
                $delivery->order->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
                break;
            case 'attempted':
                $delivery->delivery_attempted_at = now();
                $delivery->delivery_attempts = ($delivery->delivery_attempts ?? 0) + 1;
                break;
            case 'failed':
                $delivery->failure_reason = $validated['notes'];
                break;
            case 'cancelled':
                $delivery->cancelled_at = now();
                break;
        }

        $history = $delivery->status_history ?? [];
        $history[] = [
            'status' => $validated['status'],
            'old_status' => $oldStatus,
            'timestamp' => now()->toDateTimeString(),
            'notes' => $validated['notes'] ?? 'تم تحديث الحالة',
        ];
        $delivery->status_history = $history;

        $delivery->save();

        return redirect()->back()->with('success', 'تم تحديث حالة التوصيل بنجاح.');
    }

    public function updateDriver(Request $request, Delivery $delivery)
    {
        $validated = $request->validate([
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_vehicle' => 'nullable|string|max:100',
        ]);

        $delivery->update($validated);

        if ($delivery->status === 'pending') {
            $delivery->update(['status' => 'assigned', 'assigned_at' => now()]);
        }

        return redirect()->back()->with('success', 'تم تعيين السائق بنجاح.');
    }

    public function destroy(Delivery $delivery)
    {
        $delivery->delete();
        return redirect()->route('admin.deliveries.index')
            ->with('success', 'تم حذف عملية التوصيل بنجاح.');
    }

    public function assignOrder(Order $order)
    {
        $existing = Delivery::where('order_id', $order->id)->first();
        if ($existing) {
            return redirect()->route('admin.deliveries.show', $existing)
                ->with('info', 'هذا الطلب لديه عملية توصيل بالفعل.');
        }

        $order->load('user', 'items.product');
        return view('admin.deliveries.create', [
            'order' => $order,
            'pendingOrders' => collect(),
        ]);
    }
}
