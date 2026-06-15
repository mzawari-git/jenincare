<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreScene;
use App\Models\SceneHotspot;
use App\Models\SceneConnection;
use App\Models\Scene3dObject;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreSceneController extends Controller
{
    public function index()
    {
        $scenes = StoreScene::withCount('hotspots')->orderBy('sort_order')->get();
        return view('admin.store-scenes.index', compact('scenes'));
    }

    public function create()
    {
        $products = Product::where('status', 'active')->orderBy('name_ar')->get();
        $scenes = StoreScene::orderBy('sort_order')->get();
        return view('admin.store-scenes.create', compact('products', 'scenes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'aisle' => 'nullable|string|max:255',
            'image_path' => 'required|string',
            'thumbnail' => 'nullable|string',
            'video_path' => 'nullable|string',
            'map_x' => 'nullable|integer|min:0|max:65535',
            'map_y' => 'nullable|integer|min:0|max:65535',
            'sort_order' => 'nullable|integer|min:0',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($request->name_ar ?: $request->name_en);
        $data['is_active'] = $request->boolean('is_active');

        StoreScene::create($data);

        return redirect()->route('admin.store-scenes.index')
            ->with('success', 'تم إضافة المشهد بنجاح');
    }

    public function edit(StoreScene $storeScene)
    {
        $products = Product::where('status', 'active')->orderBy('name_ar')->get();
        $scenes = StoreScene::where('id', '!=', $storeScene->id)->orderBy('sort_order')->get();
        return view('admin.store-scenes.edit', compact('storeScene', 'products', 'scenes'));
    }

    public function update(Request $request, StoreScene $storeScene)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'section' => 'nullable|string|max:255',
            'aisle' => 'nullable|string|max:255',
            'image_path' => 'required|string',
            'thumbnail' => 'nullable|string',
            'video_path' => 'nullable|string',
            'map_x' => 'nullable|integer|min:0|max:65535',
            'map_y' => 'nullable|integer|min:0|max:65535',
            'sort_order' => 'nullable|integer|min:0',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['slug'] = Str::slug($request->name_ar ?: $request->name_en);
        $data['is_active'] = $request->boolean('is_active');

        $storeScene->update($data);

        return redirect()->route('admin.store-scenes.index')
            ->with('success', 'تم تحديث المشهد بنجاح');
    }

    public function destroy(StoreScene $storeScene)
    {
        $storeScene->delete();
        return redirect()->route('admin.store-scenes.index')
            ->with('success', 'تم حذف المشهد بنجاح');
    }

    public function hotspots(StoreScene $storeScene)
    {
        $storeScene->load('hotspots.product');
        $products = Product::where('status', 'active')->orderBy('name_ar')->get();
        return view('admin.store-scenes.hotspots', compact('storeScene', 'products'));
    }

    public function storeHotspot(Request $request, StoreScene $storeScene)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'pitch' => 'required|numeric',
            'yaw' => 'required|numeric',
            'label_ar' => 'nullable|string|max:255',
            'label_en' => 'nullable|string|max:255',
            'icon_type' => 'nullable|string|in:product,discount,info',
        ]);

        $storeScene->hotspots()->create($data);

        return redirect()->route('admin.store-scenes.hotspots', $storeScene)
            ->with('success', 'تم إضافة النقطة التفاعلية بنجاح');
    }

    public function destroyHotspot(SceneHotspot $hotspot)
    {
        $sceneId = $hotspot->scene_id;
        $hotspot->delete();
        return redirect()->route('admin.store-scenes.hotspots', $sceneId)
            ->with('success', 'تم حذف النقطة التفاعلية بنجاح');
    }

    public function connections(StoreScene $storeScene)
    {
        $storeScene->load('connectionsFrom.toScene', 'connectionsTo.fromScene');
        $scenes = StoreScene::where('id', '!=', $storeScene->id)->orderBy('sort_order')->get();
        return view('admin.store-scenes.connections', compact('storeScene', 'scenes'));
    }

    public function storeConnection(Request $request, StoreScene $storeScene)
    {
        $data = $request->validate([
            'to_scene_id' => 'required|exists:store_scenes,id|different:from_scene_id',
            'direction' => 'required|string|in:forward,left,right,back,up,down',
            'label_ar' => 'nullable|string|max:255',
            'label_en' => 'nullable|string|max:255',
        ]);

        $data['from_scene_id'] = $storeScene->id;

        SceneConnection::create($data);

        return redirect()->route('admin.store-scenes.connections', $storeScene)
            ->with('success', 'تم إضافة الرابط بنجاح');
    }

    public function destroyConnection(SceneConnection $connection)
    {
        $sceneId = $connection->from_scene_id;
        $connection->delete();
        return redirect()->route('admin.store-scenes.connections', $sceneId)
            ->with('success', 'تم حذف الرابط بنجاح');
    }

    // ============================================================
    // 3D Objects
    // ============================================================
    public function objects3d(StoreScene $storeScene)
    {
        $storeScene->load('objects3d');
        $objects = $storeScene->objects3d()->orderBy('sort_order')->get();
        return view('admin.store-scenes.3d-objects.index', compact('storeScene', 'objects'));
    }

    public function storeObject3d(Request $request, StoreScene $storeScene)
    {
        $data = $request->validate([
            'object_type' => 'required|string|in:product_display,shelf,wall,floor,sign,decor,lighting',
            'model_path' => 'nullable|string|max:500',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'position_z' => 'nullable|numeric',
            'rotation_x' => 'nullable|numeric',
            'rotation_y' => 'nullable|numeric',
            'rotation_z' => 'nullable|numeric',
            'scale' => 'nullable|numeric|min:0.01',
            'color' => 'nullable|string|max:50',
            'is_walkable' => 'boolean',
            'is_collision' => 'boolean',
            'label_ar' => 'nullable|string|max:255',
            'label_en' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['is_walkable'] = $request->boolean('is_walkable');
        $data['is_collision'] = $request->boolean('is_collision');
        $data['is_active'] = $request->boolean('is_active');

        $storeScene->objects3d()->create($data);

        return redirect()->route('admin.store-scenes.3d-objects', $storeScene)
            ->with('success', 'تم إضافة الكائن ثلاثي الأبعاد بنجاح');
    }

    public function updateObject3d(Request $request, StoreScene $storeScene, Scene3dObject $object)
    {
        $data = $request->validate([
            'object_type' => 'required|string|in:product_display,shelf,wall,floor,sign,decor,lighting',
            'model_path' => 'nullable|string|max:500',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'position_z' => 'nullable|numeric',
            'rotation_x' => 'nullable|numeric',
            'rotation_y' => 'nullable|numeric',
            'rotation_z' => 'nullable|numeric',
            'scale' => 'nullable|numeric|min:0.01',
            'color' => 'nullable|string|max:50',
            'is_walkable' => 'boolean',
            'is_collision' => 'boolean',
            'label_ar' => 'nullable|string|max:255',
            'label_en' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $data['is_walkable'] = $request->boolean('is_walkable');
        $data['is_collision'] = $request->boolean('is_collision');
        $data['is_active'] = $request->boolean('is_active');

        $object->update($data);

        return redirect()->route('admin.store-scenes.3d-objects', $storeScene)
            ->with('success', 'تم تحديث الكائن ثلاثي الأبعاد بنجاح');
    }

    public function destroyObject3d(StoreScene $storeScene, Scene3dObject $object)
    {
        $object->delete();
        return redirect()->route('admin.store-scenes.3d-objects', $storeScene)
            ->with('success', 'تم حذف الكائن ثلاثي الأبعاد بنجاح');
    }

    public function toggle3d(Request $request, StoreScene $storeScene)
    {
        $request->validate(['enabled' => 'required|boolean']);
        $storeScene->update(['3d_enabled' => $request->enabled]);
        return response()->json(['success' => true]);
    }
}
