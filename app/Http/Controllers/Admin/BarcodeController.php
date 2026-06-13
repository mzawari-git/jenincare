<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeController extends Controller
{
    /**
     * عرض صفحة طباعة الباركود للمنتجات
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ar', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'no_barcode') {
                $query->where(function ($q) { $q->whereNull('barcode')->orWhere('barcode', ''); });
            } elseif ($request->status === 'has_barcode') {
                $query->whereNotNull('barcode')->where('barcode', '!=', '');
            }
        }

        $products = $query->orderBy('name_ar')->paginate(50);
        $categories = \App\Models\Category::active()->orderBy('name_ar')->get();

        return view('admin.products.barcodes', compact('products', 'categories'));
    }

    /**
     * تحديث باركود منتج
     */
    public function updateBarcode(Request $request, Product $product)
    {
        $data = $request->validate([
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
        ]);

        $product->update([
            'barcode' => $data['barcode'],
            'barcode_slug' => $data['barcode'] ? 'BC-' . time() . '-' . $product->id : null,
        ]);

        return redirect()->back()->with('success', 'تم تحديث الباركود لـ ' . $product->name_ar);
    }

    /**
     * عدد المنتجات المطابقة للفلترة (لاختيار الكل)
     */
    public function countByFilters(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ar', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'no_barcode') {
                $query->where(function ($q) { $q->whereNull('barcode')->orWhere('barcode', ''); });
            } elseif ($request->status === 'has_barcode') {
                $query->whereNotNull('barcode')->where('barcode', '!=', '');
            }
        }

        return response()->json(['count' => $query->count()]);
    }

    /**
     * تصدير الباركود إلى CSV
     */
    public function exportCsv()
    {
        $products = Product::whereNotNull('barcode')->where('barcode', '!=', '')
            ->orderBy('name_ar')->get();

        $filename = 'barcodes-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            // BOM for Arabic Excel support
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Barcode', 'SKU', 'Name (AR)', 'Name (EN)', 'Price', 'Category']);
            foreach ($products as $p) {
                fputcsv($handle, [
                    $p->barcode,
                    $p->sku,
                    $p->name_ar,
                    $p->name_en,
                    $p->b2c_price,
                    $p->category?->name_ar ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * توليد باركود تلقائي لمنتجات بدون باركود
     */
    public function generateMissing()
    {
        $products = Product::where(function ($q) { $q->whereNull('barcode')->orWhere('barcode', ''); })->get();
        $count = 0;

        foreach ($products as $product) {
            $barcode = $this->generateEAN13($product->id);
            $product->update([
                'barcode' => $barcode,
                'barcode_slug' => 'BC-' . time() . '-' . $product->id,
            ]);
            $count++;
        }

        return redirect()->back()->with('success', "تم توليد باركود لـ {$count} منتج بنجاح.");
    }

    /**
     * عرض صفحة الطباعة
     */
    public function print(Request $request)
    {
        $selectAll = $request->boolean('select_all');
        $ids = $request->input('ids', []);
        $qtys = $request->input('qty', []);

        // If "select all matching" is active, fetch all matching IDs from filters
        if ($selectAll) {
            $query = Product::query();
            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name_ar', 'like', "%{$request->search}%")
                      ->orWhere('sku', 'like', "%{$request->search}%")
                      ->orWhere('barcode', 'like', "%{$request->search}%");
                });
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
        if ($request->filled('status')) {
            if ($request->status === 'no_barcode') {
                $query->where(function ($q) {
                    $q->whereNull('barcode')->orWhere('barcode', '');
                });
            } elseif ($request->status === 'has_barcode') {
                $query->whereNotNull('barcode')->where('barcode', '!=', '');
            }
        }
            $ids = $query->pluck('id')->toArray();
        }

        $layout = $request->input('layout', 'a4_24');
        $width = $request->input('width', 50);
        $height = $request->input('height', 30);
        $barcodePosition = $request->input('barcode_position', 'bottom');
        $showName = $request->boolean('show_name', true);
        $showPrice = $request->boolean('show_price', true);
        $showBrand = $request->boolean('show_brand', true);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'يرجى اختيار منتجات للطباعة.');
        }

        $products = Product::whereIn('id', $ids)->get()->keyBy('id');

        // Expand products by requested quantities
        $expanded = collect();
        foreach ($ids as $id) {
            $product = $products->get($id);
            if (!$product) continue;
            $qty = max(1, (int)($qtys[$id] ?? 1));
            for ($i = 0; $i < $qty; $i++) {
                $expanded->push($product);
            }
        }

        if ($expanded->isEmpty()) {
            return redirect()->back()->with('error', 'يرجى اختيار منتجات للطباعة.');
        }

        $totalLabels = $expanded->count();
        $siteSettings = \App\Models\Setting::pluck('value', 'key')->all();

        $generator = new BarcodeGeneratorSVG();
        foreach ($expanded as $product) {
            if ($product->barcode) {
                try {
                    $product->barcode_svg = $generator->getBarcode(trim($product->barcode), $generator::TYPE_EAN_13, 2, 80);
                } catch (\Exception $e) {
                    try {
                        $product->barcode_svg = $generator->getBarcode(trim($product->barcode), $generator::TYPE_CODE_128, 2, 80);
                    } catch (\Exception $e2) {
                        $product->barcode_svg = null;
                    }
                }
            } else {
                $product->barcode_svg = null;
            }
        }

        // Log print history
        if (class_exists(\App\Models\BarcodePrintLog::class)) {
            foreach ($ids as $id) {
                \App\Models\BarcodePrintLog::create([
                    'product_id' => $id,
                    'user_id' => auth()->id(),
                    'quantity' => (int)($qtys[$id] ?? 1),
                    'layout' => $layout,
                ]);
            }
            $now = now();
            foreach ($products as $product) {
                $product->increment('print_count');
                \App\Models\Product::where('id', $product->id)->update(['last_printed_at' => $now]);
            }
        }

        if (in_array($layout, ['thermal', 'thermal_a5', 'thermal_a6', 'thermal_custom'])) {
            return view('admin.products.barcode-print-thermal', compact(
                'layout', 'expanded', 'totalLabels', 'products', 'siteSettings', 'barcodePosition', 'showName', 'showPrice', 'showBrand', 'width', 'height'
            ));
        }

        return view('admin.products.barcode-print', compact(
            'expanded', 'totalLabels', 'products', 'layout', 'siteSettings', 'width', 'height',
            'barcodePosition', 'showName', 'showPrice', 'showBrand'
        ));
    }

    /**
     * عرض باركود SVG (للمعاينة في الجدول)
     */
    public function svg($code)
    {
        $generator = new BarcodeGeneratorSVG();
        try {
            $svg = $generator->getBarcode(trim($code), $generator::TYPE_EAN_13, 2, 60);
        } catch (\Exception $e) {
            try {
                $svg = $generator->getBarcode(trim($code), $generator::TYPE_CODE_128, 2, 60);
            } catch (\Exception $e2) {
                abort(404, 'Cannot generate barcode for: ' . $code);
            }
        }
        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    /**
     * توليد رقم EAN-13 صالح مع ضمان عدم التكرار
     */
    private function generateEAN13(int $productId): string
    {
        // Use timestamp + product ID to avoid collisions
        $base = ((int)(microtime(true) * 1000) % 900000000) + ($productId % 100000000);
        $prefix = '626';
        $attempts = 0;

        do {
            $seed = ($base + $attempts) % 1000000000;
            $middle = str_pad($seed, 9, '0', STR_PAD_LEFT);
            $code = $prefix . $middle;
            $code .= $this->calculateEAN13CheckDigit($code);
            $attempts++;
            if ($attempts > 100) {
                // Fallback: use random
                $code = $prefix . str_pad(random_int(0, 999999999), 9, '0', STR_PAD_LEFT);
                $code .= $this->calculateEAN13CheckDigit($code);
                break;
            }
        } while (\App\Models\Product::where('barcode', $code)->exists());

        return $code;
    }

    private function calculateEAN13CheckDigit(string $code): string
    {
        $sum = 0;
        $len = strlen($code);
        for ($i = 0; $i < $len; $i++) {
            $sum += ($i % 2 === 0) ? (int)$code[$i] : (int)$code[$i] * 3;
        }
        $check = (10 - ($sum % 10)) % 10;
        return (string)$check;
    }
}
