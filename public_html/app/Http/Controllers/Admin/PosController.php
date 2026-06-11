<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosSale;
use App\Models\Product;
use App\Services\OfflineConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosController extends Controller
{
    public function __construct(
        private OfflineConversionService $offlineConversion,
    ) {}

    public function index()
    {
        $todaySales = PosSale::whereDate('created_at', today())->count();
        $todayRevenue = PosSale::whereDate('created_at', today())->sum('order_total');
        $totalSales = PosSale::count();
        $totalRevenue = PosSale::sum('order_total');
        $recentSales = PosSale::latest()->take(10)->get();

        $products = Product::active()->where('stock_quantity', '>', 0)
            ->orderBy('name_ar')
            ->select('id', 'name_ar', 'name_en', 'sku', 'barcode', 'b2c_price', 'stock_quantity', 'main_image')
            ->take(50)
            ->get();

        $categories = \App\Models\Category::orderBy('name_ar')->get(['id', 'name_ar', 'name_en']);

        return view('admin.pos.index', compact(
            'todaySales', 'todayRevenue', 'totalSales', 'totalRevenue',
            'recentSales', 'products', 'categories'
        ));
    }

    public function searchProducts(Request $request)
    {
        $term = $request->get('q', '');
        $categoryId = $request->get('category_id');

        $query = Product::active()->where(function ($q) {
            $q->where('stock_quantity', '>', 0)->orWhere('allow_backorder', true);
        });

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name_ar', 'LIKE', "%{$term}%")
                  ->orWhere('name_en', 'LIKE', "%{$term}%")
                  ->orWhere('sku', 'LIKE', "%{$term}%")
                  ->orWhere('barcode', 'LIKE', "%{$term}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name_ar')
            ->select('id', 'name_ar', 'name_en', 'sku', 'barcode', 'b2c_price', 'cost_price',
                     'stock_quantity', 'reserved_quantity', 'main_image', 'main_image_webp',
                     'track_inventory')
            ->take(20)
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
                ];
            });

        return response()->json(['products' => $products]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'payment_method' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $cartItems = [];
            $subtotal = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $lineTotal = $item['price'] * $item['quantity'];
                $subtotal += $lineTotal;

                $cartItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                    'total' => $lineTotal,
                ];

                if ($product->track_inventory) {
                    if ($product->available_quantity < $item['quantity']) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "المنتج '{$product->name}' غير متوفر بالكمية المطلوبة (المتوفر: {$product->available_quantity})",
                        ], 422);
                    }
                }
            }

            $posSaleId = 'POS-' . strtoupper(\Illuminate\Support\Str::random(10));

            $sale = PosSale::create([
                'pos_sale_id' => $posSaleId,
                'store_id' => 'admin',
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'order_total' => $subtotal,
                'subtotal' => $subtotal,
                'currency' => 'ILS',
                'items' => $cartItems,
                'payment_method' => $data['payment_method'],
                'sale_at' => now(),
                'user_id' => auth()->id(),
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->track_inventory) {
                    $product->confirmSale($item['quantity']);
                }
            }

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

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل البيع: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function recentSales()
    {
        $sales = PosSale::latest()->take(20)->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'pos_sale_id' => $s->pos_sale_id,
                'customer_name' => $s->customer_name,
                'total' => (float) $s->order_total,
                'payment_method' => $s->payment_method,
                'items_count' => is_array($s->items) ? count($s->items) : 0,
                'created_at' => $s->created_at->format('Y-m-d H:i'),
            ];
        });

        return response()->json(['sales' => $sales]);
    }
}
