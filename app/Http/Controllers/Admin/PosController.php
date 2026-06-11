<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosSale;
use App\Models\Product;
use App\Models\SuspendedCart;
use App\Models\User;
use App\Models\Category;
use App\Services\OfflineConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PosController extends Controller
{
    public function __construct(
        private OfflineConversionService $offlineConversion,
    ) {}

    public function index()
    {
        $stats = PosSale::selectRaw('
                COUNT(*) as total_sales,
                COALESCE(SUM(order_total), 0) as total_revenue,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END), 0) as today_sales,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN order_total ELSE 0 END), 0) as today_revenue
            ')->first();
        $todaySales = $stats->today_sales;
        $todayRevenue = $stats->today_revenue;
        $totalSales = $stats->total_sales;
        $totalRevenue = $stats->total_revenue;
        $recentSales = PosSale::latest()->take(10)->get();

        $products = Product::active()->where(function ($q) {
                $q->where('track_inventory', false)
                  ->orWhere('stock_quantity', '>', 0)
                  ->orWhere('allow_backorder', true);
            })
            ->orderBy('name_ar')
            ->select('id', 'name_ar', 'name_en', 'sku', 'barcode', 'b2c_price', 'stock_quantity', 'main_image', 'discount_percentage', 'track_inventory')
            ->take(40)
            ->get();

        $categories = Category::orderBy('name_ar')->get(['id', 'name_ar', 'name_en']);

        return view('admin.pos.index', compact(
            'todaySales', 'todayRevenue', 'totalSales', 'totalRevenue',
            'recentSales', 'products', 'categories'
        ));
    }

    public function searchProducts(Request $request)
    {
        $term = $request->get('q', '');
        $categoryId = $request->get('category_id');
        $page = (int) $request->get('page', 1);
        $perPage = 40;
        $skip = ($page - 1) * $perPage;

        $query = Product::active()->where(function ($q) {
            $q->where('track_inventory', false)
              ->orWhere('stock_quantity', '>', 0)
              ->orWhere('allow_backorder', true);
        });

        if ($term) {
            $safeTerm = str_replace(['%', '_'], ['\\%', '\\_'], $term);
            $query->where(function ($q) use ($safeTerm) {
                $q->where('name_ar', 'LIKE', "%{$safeTerm}%")
                  ->orWhere('name_en', 'LIKE', "%{$safeTerm}%")
                  ->orWhere('sku', 'LIKE', "%{$safeTerm}%")
                  ->orWhere('barcode', 'LIKE', "%{$safeTerm}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($request->get('offers')) {
            $query->where('discount_percentage', '>', 0);
        }

        if ($request->get('bestseller')) {
            $query->where('is_bestseller', true);
        }

        $total = $query->count();
        if ($request->get('bestseller')) {
            $products = $query->orderBy('sold_count', 'desc')->orderBy('name_ar');
        } else {
            $products = $query->orderBy('name_ar');
        }
        $products = $products
            ->select('id', 'name_ar', 'name_en', 'sku', 'barcode', 'b2c_price', 'cost_price',
                     'stock_quantity', 'reserved_quantity', 'main_image', 'main_image_webp',
                     'track_inventory', 'discount_percentage')
            ->skip($skip)
            ->take($perPage + 1)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'name_ar' => $p->name_ar,
                    'name_en' => $p->name_en,
                    'sku' => $p->sku,
                    'barcode' => $p->barcode,
                    'price' => (float) $p->b2c_price,
                    'cost_price' => (float) $p->cost_price,
                    'stock' => $p->available_quantity,
                    'image' => $p->main_image_url,
                    'track_inventory' => $p->track_inventory,
                    'discount_percentage' => (float) $p->discount_percentage,
                ];
            });

        $hasMore = $products->count() > $perPage;
        if ($hasMore) $products->pop();

        return response()->json([
            'products' => $products,
            'has_more' => $hasMore,
            'page' => $page,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'payment_method' => 'required|string|max:50',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string|in:fixed,percent',
            'tax_enabled' => 'nullable|boolean',
            'tax_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.item_discount' => 'nullable|numeric|min:0',
            'items.*.item_discount_type' => 'nullable|string|in:fixed,percent',
            'split_payments' => 'nullable|array',
            'split_payments.*.method' => 'required|string|max:50',
            'split_payments.*.amount' => 'required|numeric|min:0',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        // Idempotency check — prevent duplicate submissions
        if (!empty($data['idempotency_key'])) {
            $existing = PosSale::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل البيع مسبقاً',
                    'data' => [
                        'id' => $existing->id,
                        'pos_sale_id' => $existing->pos_sale_id,
                        'total' => $existing->order_total,
                    ],
                ], 200);
            }
        }

        DB::beginTransaction();
        try {
            $cartItems = [];
            $subtotal = 0;
            $totalItemDiscount = 0;

            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();
                $lineTotal = $item['price'] * $item['quantity'];
                $itemDiscount = (float) ($item['item_discount'] ?? 0);
                if ($itemDiscount > 0) {
                    if (($item['item_discount_type'] ?? 'fixed') === 'percent') {
                        $itemDiscount = min($lineTotal * ($itemDiscount / 100), $lineTotal);
                    } else {
                        $itemDiscount = min($itemDiscount, $lineTotal);
                    }
                }
                $discountedLineTotal = $lineTotal - $itemDiscount;
                $subtotal += $lineTotal;
                $totalItemDiscount += $itemDiscount;

                $cartItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'total' => max(0, $discountedLineTotal),
                    'item_discount' => $itemDiscount,
                    'item_discount_type' => $item['item_discount_type'] ?? 'fixed',
                ];

                if ($product->track_inventory) {
                    if ($product->available_quantity < $item['quantity']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "المنتج '{$product->name}' غير متوفر بالكمية المطلوبة (المتوفر: {$product->available_quantity})",
                        ], 422);
                    }
                    $product->confirmSale($item['quantity']);
                }
            }

            $discount = (float) ($data['discount'] ?? 0);
            if ($discount > 0 && ($data['discount_type'] ?? 'fixed') === 'percent') {
                $discount = min($subtotal * ($discount / 100), $subtotal);
            }
            $taxAmount = $data['tax_enabled'] ? (float) ($data['tax_amount'] ?? 0) : 0;
            $orderTotal = ($subtotal - $discount - $totalItemDiscount) + $taxAmount;

            $posSaleId = 'POS-' . strtoupper(\Illuminate\Support\Str::random(10));
            // Ensure uniqueness with retry
            $attempts = 0;
            while (PosSale::where('pos_sale_id', $posSaleId)->exists() && $attempts < 5) {
                $posSaleId = 'POS-' . strtoupper(\Illuminate\Support\Str::random(12));
                $attempts++;
            }

            $sale = PosSale::create([
                'pos_sale_id' => $posSaleId,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'store_id' => 'admin',
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'order_total' => $orderTotal,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'tax_rate' => $data['tax_enabled'] ? (float) ($data['tax_rate'] ?? 0) : 0,
                'currency' => 'ILS',
                'items' => $cartItems,
                'payment_method' => $data['split_payments'] ? 'split' : $data['payment_method'],
                'notes' => $data['notes'] ?? null,
                'split_payments' => $data['split_payments'] ?? null,
                'status' => 'completed',
                'sale_at' => now(),
                'user_id' => auth()->id(),
            ]);

            try {
                $this->offlineConversion->matchCustomer($sale);
            } catch (\Exception $e) {
                Log::warning('POS offline conversion failed', ['error' => $e->getMessage()]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل البيع بنجاح',
                'data' => [
                    'id' => $sale->id,
                    'pos_sale_id' => $sale->pos_sale_id,
                    'total' => $sale->order_total,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS sale failed', ['error' => $e->getMessage()]);

            $message = config('app.debug') ? 'حدث خطأ أثناء تسجيل البيع: ' . $e->getMessage() : 'حدث خطأ أثناء تسجيل البيع';
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 500);
        }
    }

    public function recentSales()
    {
        $sales = PosSale::where('status', '!=', 'cancelled')->latest()->take(50)->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'pos_sale_id' => $s->pos_sale_id,
                'customer_name' => $s->customer_name,
                'total' => (float) $s->order_total,
                'payment_method' => $s->payment_method,
                'items_count' => is_array($s->items) ? count($s->items) : 0,
                'status' => $s->status,
                'created_at' => $s->created_at->format('Y-m-d H:i'),
            ];
        });

        return response()->json(['sales' => $sales]);
    }

    public function suspendCart(Request $request)
    {
        $data = $request->validate([
            'cart_data' => 'required|array|min:1',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $total = collect($data['cart_data'])->sum(fn($item) => $item['price'] * $item['quantity']);

        $cart = SuspendedCart::create([
            'user_id' => auth()->id(),
            'cart_data' => $data['cart_data'],
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'notes' => $data['notes'] ?? null,
            'item_count' => count($data['cart_data']),
            'total' => $total,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعليق الطلب بنجاح',
            'data' => ['id' => $cart->id],
        ]);
    }

    public function suspendedCarts()
    {
        $carts = SuspendedCart::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'customer_name' => $c->customer_name,
                'item_count' => $c->item_count,
                'total' => (float) $c->total,
                'notes' => $c->notes,
                'created_at' => $c->created_at->format('Y-m-d H:i'),
                'cart_data' => $c->cart_data,
                'customer_phone' => $c->customer_phone,
                'customer_email' => $c->customer_email,
                'payment_method' => $c->payment_method,
            ]);

        return response()->json(['carts' => $carts]);
    }

    public function restoreCart(int $id)
    {
        $cart = SuspendedCart::where('user_id', auth()->id())->findOrFail($id);
        $cartData = $cart->cart_data;
        // Enrich with current stock levels
        if (is_array($cartData)) {
            $productIds = array_column($cartData, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            foreach ($cartData as &$item) {
                $pid = $item['product_id'] ?? null;
                $item['stock'] = $pid && isset($products[$pid]) ? $products[$pid]->available_quantity : 0;
                $item['image'] = $pid && isset($products[$pid]) ? $products[$pid]->main_image_url : ($item['image'] ?? '');
            }
        }
        return response()->json([
            'success' => true,
            'data' => [
                'cart_data' => $cartData,
                'customer_name' => $cart->customer_name,
                'customer_phone' => $cart->customer_phone,
                'customer_email' => $cart->customer_email,
                'payment_method' => $cart->payment_method,
            ],
        ]);
    }

    public function deleteSuspendedCart(int $id)
    {
        $cart = SuspendedCart::where('user_id', auth()->id())->findOrFail($id);
        $cart->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف الطلب المعلق']);
    }

    public function getSale(string $posSaleId)
    {
        $sale = PosSale::where('pos_sale_id', $posSaleId)->firstOrFail();
        return response()->json([
            'sale' => [
                'id' => $sale->id,
                'pos_sale_id' => $sale->pos_sale_id,
                'items' => $sale->items,
                'subtotal' => (float) $sale->subtotal,
                'order_total' => (float) $sale->order_total,
                'discount_amount' => (float) $sale->discount_amount,
                'customer_name' => $sale->customer_name,
                'customer_phone' => $sale->customer_phone,
                'customer_email' => $sale->customer_email,
                'payment_method' => $sale->payment_method,
                'notes' => $sale->notes,
                'created_at' => $sale->created_at->toISOString(),
                'user_id' => $sale->user_id,
            ],
        ]);
    }

    // ========== EDIT SALE ==========

    public function editSale(Request $request, string $posSaleId)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.price' => 'required|numeric|min:0',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            $sale = PosSale::where('pos_sale_id', $posSaleId)->firstOrFail();

            // Restore original inventory (reverse previous deduction)
            if ($sale->items && is_array($sale->items)) {
                foreach ($sale->items as $oldItem) {
                    $product = Product::where('id', $oldItem['product_id'])->lockForUpdate()->first();
                    if ($product && $product->track_inventory) {
                        $product->increment('stock_quantity', $oldItem['quantity']);
                    }
                }
            }

            // Build new items and deduct new inventory
            $cartItems = [];
            $subtotal = 0;
            $totalItemDiscount = 0;
            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();
                $lineTotal = $item['price'] * $item['quantity'];
                $subtotal += $lineTotal;

                $itemDiscount = (float) ($item['item_discount'] ?? 0);
                if ($itemDiscount > 0) {
                    if (($item['item_discount_type'] ?? 'fixed') === 'percent') {
                        $itemDiscount = min($lineTotal * ($itemDiscount / 100), $lineTotal);
                    } else {
                        $itemDiscount = min($itemDiscount, $lineTotal);
                    }
                }
                $totalItemDiscount += $itemDiscount;

                $cartItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'total' => max(0, $lineTotal - $itemDiscount),
                    'item_discount' => $itemDiscount,
                    'item_discount_type' => $item['item_discount_type'] ?? 'fixed',
                ];

                if ($product->track_inventory && $item['quantity'] > 0) {
                    if ($product->available_quantity < $item['quantity']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "المنتج '{$product->name}' غير متوفر بالكمية المطلوبة (المتوفر: {$product->available_quantity})",
                        ], 422);
                    }
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Remove zero-quantity items, recalc totals
            $cartItems = array_values(array_filter($cartItems, fn($i) => $i['quantity'] > 0));
            if (empty($cartItems)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'يجب أن تحتوي الفاتورة على منتج واحد على الأقل'], 422);
            }

            // Recalculate discount proportionally if original had one
            $discount = $sale->discount_amount;
            if ($discount > 0 && $sale->subtotal > 0) {
                $discount = min($subtotal * ($discount / $sale->subtotal), $subtotal);
            } else {
                $discount = 0;
            }

            // Recalculate tax based on updated subtotal
            $taxAmount = $sale->tax_amount;
            $taxRate = (float) $sale->tax_rate;
            if ($taxRate > 0 && $sale->subtotal > 0) {
                $taxAmount = $subtotal * ($taxRate / 100);
            }

            $orderTotal = ($subtotal - $discount - $totalItemDiscount) + $taxAmount;

            $sale->update([
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'order_total' => $orderTotal,
                'customer_name' => $data['customer_name'] ?? $sale->customer_name,
                'customer_phone' => $data['customer_phone'] ?? $sale->customer_phone,
                'customer_email' => $data['customer_email'] ?? $sale->customer_email,
                'notes' => $data['notes'] ?? $sale->notes,
                'payment_method' => $data['payment_method'] ?? $sale->payment_method,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تعديل الفاتورة بنجاح',
                'data' => ['pos_sale_id' => $sale->pos_sale_id, 'total' => $sale->order_total],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('POS edit failed', ['error' => $e->getMessage()]);
            $msg = config('app.debug') ? 'فشل التعديل: ' . $e->getMessage() : 'فشل التعديل';
            return response()->json(['success' => false, 'message' => $msg], 500);
        }
    }

    // ========== DELETE / VOID SALE ==========

    public function deleteSale(Request $request, string $posSaleId)
    {
        $data = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $sale = PosSale::where('pos_sale_id', $posSaleId)->firstOrFail();

            // Restore inventory for all items
            if ($sale->items && is_array($sale->items)) {
                foreach ($sale->items as $oldItem) {
                    $product = Product::find($oldItem['product_id']);
                    if ($product && $product->track_inventory && ($oldItem['quantity'] ?? 0) > 0) {
                        $product->increment('stock_quantity', $oldItem['quantity']);
                    }
                }
            }

            // Mark as cancelled with audit trail
            $sale->update([
                'status' => 'cancelled',
                'cancelled_by_user_id' => auth()->id(),
                'notes' => ($data['reason'] ?? 'بدون سبب'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الفاتورة بنجاح',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = config('app.debug') ? 'فشل الإلغاء: ' . $e->getMessage() : 'فشل الإلغاء';
            return response()->json(['success' => false, 'message' => $msg], 500);
        }
    }

    public function printReceipt(string $posSaleId)
    {
        $sale = PosSale::where('pos_sale_id', $posSaleId)->firstOrFail();
        $siteSettings = \App\Models\Setting::pluck('value', 'key')->all();
        return view('admin.pos.thermal-receipt', compact('sale', 'siteSettings'));
    }

    // ========== CUSTOMER MANAGEMENT ==========

    public function searchCustomers(Request $request)
    {
        $term = $request->get('q', '');
        $query = User::where('role', 'customer');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('phone', 'LIKE', "%{$term}%")
                  ->orWhere('email', 'LIKE', "%{$term}%");
            });
        }

        $customers = $query->orderBy('name')->take(20)->get(['id', 'name', 'phone', 'email']);

        return response()->json(['customers' => $customers]);
    }

    public function createCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'email' => 'nullable|email|max:255|unique:users,email',
        ]);

        $customer = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => bcrypt(Str::random(16)),
            'role' => 'customer',
        ]);

        return response()->json([
            'success' => true,
            'customer' => ['id' => $customer->id, 'name' => $customer->name, 'phone' => $customer->phone, 'email' => $customer->email],
        ]);
    }

    public function customerHistory(int $id)
    {
        $customer = User::findOrFail($id);
        $sales = PosSale::where(function ($q) use ($customer) {
                $q->where('customer_name', $customer->name)
                  ->orWhere('customer_phone', $customer->phone)
                  ->orWhere('customer_email', $customer->email);
            })
            ->latest()
            ->take(20)
            ->get()
            ->map(fn($s) => [
                'pos_sale_id' => $s->pos_sale_id,
                'total' => (float) $s->order_total,
                'items_count' => is_array($s->items) ? count($s->items) : 0,
                'created_at' => $s->created_at->format('Y-m-d H:i'),
                'payment_method' => $s->payment_method,
            ]);

        return response()->json([
            'customer' => ['id' => $customer->id, 'name' => $customer->name, 'phone' => $customer->phone, 'email' => $customer->email],
            'sales' => $sales,
            'total_spent' => (float) $sales->sum('total'),
        ]);
    }

    // ========== QUICK PRODUCT CREATION ==========

    public function quickCreateProduct(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'b2c_price' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $product = Product::create([
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_ar'],
            'base_price' => $data['b2c_price'],
            'b2c_price' => $data['b2c_price'],
            'b2b_price' => $data['b2c_price'],
            'sku' => $data['sku'] ?? 'QUICK-' . strtoupper(Str::random(8)),
            'barcode' => $data['barcode'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'stock_quantity' => 0,
            'track_inventory' => false,
            'status' => 'active',
            'show_in_b2c' => false,
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->b2c_price,
                'stock' => 999,
                'image' => '',
                'sku' => $product->sku,
                'barcode' => $product->barcode,
            ],
        ]);
    }

    // ========== FAVORITES ==========

    public function getFavorites()
    {
        $user = auth()->user();
        $favIds = json_decode($user->pos_favorites ?? '[]', true);
        if (empty($favIds)) {
            return response()->json(['products' => []]);
        }

        $products = Product::whereIn('id', $favIds)
            ->active()
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->b2c_price,
                'stock' => $p->available_quantity,
                'image' => $p->main_image_url,
                'sku' => $p->sku,
            ]);

        return response()->json(['products' => $products]);
    }

    public function toggleFavorite(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $user = auth()->user();
        $favIds = json_decode($user->pos_favorites ?? '[]', true);

        if (in_array($data['product_id'], $favIds)) {
            $favIds = array_values(array_diff($favIds, [$data['product_id']]));
            $added = false;
        } else {
            $favIds[] = $data['product_id'];
            $added = true;
        }

        $user->pos_favorites = json_encode(array_slice($favIds, -50));
        $user->save();

        return response()->json(['success' => true, 'added' => $added]);
    }

    // ========== REFUND ==========

    public function processRefund(Request $request)
    {
        $data = $request->validate([
            'sale_id' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $sale = PosSale::where('pos_sale_id', $data['sale_id'])->firstOrFail();
            $origItems = is_array($sale->items) ? $sale->items : [];
            if ($sale->status === 'refunded') {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'هذه الفاتورة تم إرجاعها بالفعل'], 422);
            }

            // Build lookup of original items by product_id
            $origByProduct = [];
            foreach ($origItems as $oi) {
                $pid = $oi['product_id'] ?? $oi['id'] ?? null;
                if ($pid) {
                    $origByProduct[$pid] = ($origByProduct[$pid] ?? 0) + ($oi['quantity'] ?? 0);
                }
            }

            // Calculate total previously refunded for this sale
            $prevRefunds = PosSale::where('parent_sale_id', $data['sale_id'])->get();
            $prevRefundedByProduct = [];
            foreach ($prevRefunds as $pr) {
                $prItems = is_array($pr->items) ? $pr->items : [];
                foreach ($prItems as $pri) {
                    $pid = $pri['product_id'] ?? $pri['id'] ?? null;
                    if ($pid) {
                        $prevRefundedByProduct[$pid] = ($prevRefundedByProduct[$pid] ?? 0) + ($pri['quantity'] ?? 0);
                    }
                }
            }

            $refundItems = [];
            $refundTotal = 0;

            foreach ($data['items'] as $item) {
                $pid = (int) $item['product_id'];
                $origQty = $origByProduct[$pid] ?? 0;
                $alreadyRefunded = $prevRefundedByProduct[$pid] ?? 0;
                $refundableQty = max(0, $origQty - $alreadyRefunded);

                if ($item['quantity'] > $refundableQty) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "المنتج ID {$pid} يمكن إرجاع {$refundableQty} فقط (طلب {$item['quantity']})",
                    ], 422);
                }

                // Use price from original sale, not current price
                $origPrice = 0;
                foreach ($origItems as $oi) {
                    $oiPid = $oi['product_id'] ?? $oi['id'] ?? null;
                    if ((int) $oiPid === $pid) {
                        $origPrice = (float) ($oi['price'] ?? 0);
                        break;
                    }
                }

                $product = Product::find($pid);
                if ($product && $product->track_inventory) {
                    $product->increment('stock_quantity', $item['quantity']);
                }

                $lineTotal = (float) $item['quantity'] * $origPrice;
                $refundTotal += $lineTotal;
                $refundItems[] = [
                    'product_id' => $pid,
                    'name' => $product->name ?? ('Product #' . $pid),
                    'quantity' => (int) $item['quantity'],
                    'price' => $origPrice,
                    'total' => $lineTotal,
                ];
            }

            $newStatus = 'refunded';
            $refundNotes = 'مرتجع من فاتورة: ' . $data['sale_id'] . ($data['reason'] ? ' | سبب: ' . $data['reason'] : '');

            $rfdSaleId = 'RFD-' . strtoupper(Str::random(10));
            $attempts = 0;
            while (PosSale::where('pos_sale_id', $rfdSaleId)->exists() && $attempts < 5) {
                $rfdSaleId = 'RFD-' . strtoupper(Str::random(12));
                $attempts++;
            }

            $refundSale = PosSale::create([
                'pos_sale_id' => $rfdSaleId,
                'parent_sale_id' => $data['sale_id'],
                'store_id' => 'admin',
                'customer_name' => $sale->customer_name,
                'customer_phone' => $sale->customer_phone,
                'order_total' => -$refundTotal,
                'subtotal' => 0,
                'currency' => 'ILS',
                'items' => $refundItems,
                'payment_method' => 'refund',
                'sale_at' => now(),
                'user_id' => auth()->id(),
                'notes' => $refundNotes,
                'status' => 'refunded',
            ]);

            $sale->update(['status' => $newStatus]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تمت عملية الإرجاع بنجاح',
                'data' => ['pos_sale_id' => $refundSale->pos_sale_id, 'refund_total' => $refundTotal],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = config('app.debug') ? 'فشل الإرجاع: ' . $e->getMessage() : 'فشل الإرجاع';
            return response()->json(['success' => false, 'message' => $msg], 500);
        }
    }
}
