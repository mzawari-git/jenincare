<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(20);

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'inactive' => Product::where('status', '!=', 'active')->count(),
            'low_stock' => Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10)->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
            'in_stock' => Product::where('stock_quantity', '>', 10)->count(),
            'total_value' => Product::where('status', 'active')->sum(\DB::raw('b2c_price * stock_quantity')),
            'categories' => \App\Models\Category::count(),
            'featured' => Product::where('is_featured', true)->count(),
        ];

        return view('admin.products.index', compact('products', 'stats'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|unique:products,slug',
            'sku' => 'nullable|string|unique:products,sku',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'main_image' => 'nullable|string|max:255',
            'b2c_price' => 'nullable|numeric|min:0',
            'b2b_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'stock_status' => 'nullable|string|in:in_stock,low_stock,out_of_stock,pre_order',
            'track_inventory' => 'nullable|boolean',
            'allow_backorder' => 'nullable|boolean',
            'status' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            'is_bestseller' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:320',
            'meta_keywords' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
        ]);

        $data['tenant_id'] = 1;
        $data['show_in_b2c'] = true;
        $data['show_in_b2b'] = true;

        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }

        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|unique:products,slug,' . $product->id,
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'main_image' => 'nullable|string|max:255',
            'b2c_price' => 'nullable|numeric|min:0',
            'b2b_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_alert' => 'nullable|integer|min:0',
            'stock_status' => 'nullable|string|in:in_stock,low_stock,out_of_stock,pre_order',
            'track_inventory' => 'nullable|boolean',
            'allow_backorder' => 'nullable|boolean',
            'status' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            'is_bestseller' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:320',
            'meta_keywords' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
        ]);

        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }

        $product->update($data);
        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function import()
    {
        return view('admin.products.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $import = new ProductsImport;
        Excel::import($import, $request->file('file'));

        $imported = $import->getImportedCount();
        $skipped = $import->getSkippedCount();
        $importErrors = $import->getErrors();
        $duplicates = $import->getDuplicates();

        $msg = "تم استيراد {$imported} منتج بنجاح";
        if ($skipped > 0) $msg .= " (تخطي {$skipped})";
        if (count($duplicates) > 0) {
            $dupInfo = [];
            foreach (array_slice($duplicates, 0, 5) as $d) {
                $dupInfo[] = "{$d['name']} (SKU: {$d['sku']})";
            }
            $msg .= ' | مكررات: ' . implode(' · ', $dupInfo);
            if (count($duplicates) > 5) $msg .= ' ... +' . (count($duplicates) - 5) . ' آخرين';
        }

        return redirect()->route('admin.products.index')
            ->with('success', $msg);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="products_template.csv"',
        ];

        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

        $columns = [
            'product_name_ar', 'product_name', 'product_barcode', 'category',
            'product_price_1', 'product_price_2', 'product_price_3', 'product_cost', 'product_stock',
            'product_description', 'product_image_url_1', 'product_is_active',
        ];

        $sample = [
            'شامبو ارغان طبيعي', 'Argan Natural Shampoo', 'ARG-SH-001', 'Shampoo',
            '180', '150', '120', '100', '50',
            'شامبو بزيت الأرغان الطبيعي للعناية بالشعر', 'https://example.com/image.jpg', '1',
        ];

        $callback = function () use ($bom, $columns, $sample) {
            $file = fopen('php://output', 'w');
            fwrite($file, $bom);
            fputcsv($file, $columns);
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->streamDownload($callback, 'products_template.csv', $headers);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}
