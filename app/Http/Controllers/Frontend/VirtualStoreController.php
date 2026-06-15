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
        return view('frontend.virtual-store.3d-store');
    }

    public function store3dScene($slug)
    {
        $scene = StoreScene::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('frontend.virtual-store.3d-store', compact('scene'));
    }
}
