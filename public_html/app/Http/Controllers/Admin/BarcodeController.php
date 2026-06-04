<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

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
                $query->whereNull('barcode')->orWhere('barcode', '');
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
     * توليد باركود تلقائي لمنتجات بدون باركود
     */
    public function generateMissing()
    {
        $products = Product::whereNull('barcode')->orWhere('barcode', '')->get();
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
     * عرض صفحة الطباعة A4
     */
    public function print(Request $request)
    {
        $ids = $request->input('ids', []);
        $layout = $request->input('layout', 'a4_24'); // a4_24, a4_12, a4_6

        if (empty($ids)) {
            return redirect()->back()->with('error', 'يرجى اختيار منتجات للطباعة.');
        }

        $products = Product::whereIn('id', $ids)->get();
        $siteSettings = \App\Models\Setting::pluck('value', 'key')->all();

        return view('admin.products.barcode-print', compact('products', 'layout', 'siteSettings'));
    }

    /**
     * توليد رقم EAN-13 صالح
     */
    private function generateEAN13(int $seed): string
    {
        $prefix = '626'; // رمز دولة فلسطين (مثال)
        $middle = str_pad($seed % 100000000, 8, '0', STR_PAD_LEFT);
        $code = $prefix . $middle;
        $code .= $this->calculateEAN13CheckDigit($code);
        return $code;
    }

    private function calculateEAN13CheckDigit(string $code): string
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += ($i % 2 === 0) ? (int)$code[$i] : (int)$code[$i] * 3;
        }
        $check = (10 - ($sum % 10)) % 10;
        return (string)$check;
    }
}
