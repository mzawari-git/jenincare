<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreScene;
use App\Models\SceneHotspot;
use Illuminate\Http\Request;

class VirtualStoreController extends Controller
{
    public function scenes()
    {
        $scenes = StoreScene::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name_ar', 'name_en', 'slug', 'section', 'aisle', 'image_path', 'thumbnail', 'map_x', 'map_y']);

        return response()->json(['data' => $scenes]);
    }

    public function scene($id)
    {
        $scene = StoreScene::where('id', $id)
            ->where('is_active', true)
            ->with(['hotspots' => function ($q) {
                $q->where('is_active', true)->with('product:id,name_ar,name_en,slug,main_image,b2c_price');
            }, 'connectionsFrom.toScene:id,name_ar,name_en,slug',
               'connectionsTo.fromScene:id,name_ar,name_en,slug'])
            ->first();

        if (!$scene) {
            return response()->json(['error' => 'Scene not found'], 404);
        }

        return response()->json(['data' => $scene]);
    }

    public function hotspotProducts(Request $request)
    {
        $request->validate(['scene_id' => 'required|exists:store_scenes,id']);

        $hotspots = SceneHotspot::where('scene_id', $request->scene_id)
            ->where('is_active', true)
            ->with('product:id,name_ar,name_en,slug,main_image,b2c_price,discount_percentage,discount_amount,discount_starts_at,discount_ends_at,stock_quantity,reserved_quantity')
            ->get()
            ->map(function ($hotspot) {
                $product = $hotspot->product;
                if (!$product) return null;
                return [
                    'hotspot_id' => $hotspot->id,
                    'pitch' => $hotspot->pitch,
                    'yaw' => $hotspot->yaw,
                    'label_ar' => $hotspot->label_ar,
                    'label_en' => $hotspot->label_en,
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'main_image_url' => $product->main_image_url,
                        'price' => $product->getCurrentPrice(),
                        'is_on_sale' => $product->is_on_sale,
                        'available_quantity' => $product->available_quantity,
                    ],
                ];
            })
            ->filter()
            ->values();

        return response()->json(['data' => $hotspots]);
    }
}
