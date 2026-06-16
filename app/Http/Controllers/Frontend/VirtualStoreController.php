<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\StoreScene;
use App\Models\SceneHotspot;
use App\Models\Product;
use Illuminate\Http\Request;

class VirtualStoreController extends Controller
{
    public function index()
    {
        $scenes = StoreScene::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('frontend.virtual-store.index', compact('scenes'));
    }

    public function scene($slug)
    {
        $scene = StoreScene::where('slug', $slug)
            ->where('is_active', true)
            ->with(['hotspots.product', 'connectionsFrom.toScene', 'connectionsTo.fromScene'])
            ->firstOrFail();

        $scenes = StoreScene::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name_ar', 'name_en', 'slug', 'thumbnail']);

        return view('frontend.virtual-store.scene', compact('scene', 'scenes'));
    }

    public function hotspotProduct(Request $request)
    {
        $request->validate(['hotspot_id' => 'required|exists:scene_hotspots,id']);

        $hotspot = SceneHotspot::with('product')->findOrFail($request->hotspot_id);
        $product = $hotspot->product;

        if (!$product || $product->status !== 'active') {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->getCurrentPrice(),
            'final_b2c_price' => $product->final_b2c_price,
            'main_image_url' => $product->main_image_url,
            'is_on_sale' => $product->is_on_sale,
            'slug' => $product->slug,
            'available_quantity' => $product->available_quantity,
        ]);
    }

    public function store3d()
    {
        // The 3D store is a self-contained HTML page (no layout needed)
        return response(view('frontend.virtual-store.3d-store', [
            'csrf_token' => csrf_token(),
            'shelfProducts' => $this->shelfProducts(),
        ]));
    }

    /**
     * Real, on-sale-aware site products grouped into the 3D store's four zones
     * (left / right / back / island). Each item carries a real slug + image so
     * the shelves and product card link to the actual product page.
     */
    protected function shelfProducts(): array
    {
        $zones = ['left' => [], 'right' => [], 'back' => [], 'island' => []];

        $keywords = [
            'left'   => ['بشرة', 'عناية', 'فيتامين', 'كريم', 'سيروم', 'skin', 'serum', 'cream', 'vitamin'],
            'right'  => ['شعر', 'صبغة', 'كيراتين', 'hair', 'color', 'keratin'],
            'back'   => ['عطر', 'مسك', 'هدايا', 'هدية', 'perfume', 'gift', 'oud', 'musk'],
            'island' => ['مكياج', 'شفاه', 'ظل', 'ريمل', 'كونسيلر', 'makeup', 'lip', 'mascara'],
        ];

        $products = Product::active()
            ->showInB2C()
            ->with('category:id,name_ar,name_en')
            ->whereNotNull('main_image')
            ->orderBy('is_featured', 'desc')
            ->orderBy('sales_count', 'desc')
            ->take(120)
            ->get();

        $fallbackOrder = array_keys($zones);
        $rr = 0;

        foreach ($products as $p) {
            $catName = ($p->category?->name_ar ?? '') . ' ' . ($p->category?->name_en ?? '');
            $target = null;

            foreach ($keywords as $zone => $kwList) {
                foreach ($kwList as $kw) {
                    if (mb_stripos($catName, $kw) !== false) {
                        $target = $zone;
                        break 2;
                    }
                }
            }

            if ($target === null) {
                $target = $fallbackOrder[$rr % count($fallbackOrder)];
                $rr++;
            }

            $zones[$target][] = [
                'id'    => $p->id,
                'name'  => $p->name,
                'price' => round($p->getCurrentPrice()),
                'old'   => $p->is_on_sale ? round($p->b2c_price) : null,
                'slug'  => $p->slug,
                'image' => $p->main_image_url ?? '',
                'zone'  => $p->category?->name_ar ?? '',
            ];
        }

        // Drop empty zones so the view falls back to demo data only where needed.
        return array_filter($zones, fn($items) => count($items) > 0);
    }

    public function store3dScene($slug)
    {
        $scene = StoreScene::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('frontend.virtual-store.3d-store', compact('scene'));
    }
}
