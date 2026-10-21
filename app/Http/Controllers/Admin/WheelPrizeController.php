<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WheelPrize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WheelPrizeController extends Controller
{
    public function index()
    {
        $prizes = WheelPrize::orderBy('sort_order')->get();
        return view('admin.wheel-prizes.index', compact('prizes'));
    }

    public function create()
    {
        return view('admin.wheel-prizes.form');
    }

    public function store(Request $request)
    {
        $data = $this->validatePrize($request);

        if ($request->type === 'product' && $request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('wheel-prizes', 'public');
        }

        $maxSort = WheelPrize::max('sort_order') ?? 0;
        $data['sort_order'] = $maxSort + 1;
        $data['is_active'] = $request->boolean('is_active', true);

        WheelPrize::create($data);

        return redirect()->route('admin.wheel-prizes.index')->with('success', 'تم إضافة العنصر بنجاح.');
    }

    public function edit(WheelPrize $wheelPrize)
    {
        return view('admin.wheel-prizes.form', compact('wheelPrize'));
    }

    public function update(Request $request, WheelPrize $wheelPrize)
    {
        $data = $this->validatePrize($request, $wheelPrize);

        if ($request->type === 'product') {
            if ($request->hasFile('image')) {
                if ($wheelPrize->image) {
                    Storage::disk('public')->delete($wheelPrize->image);
                }
                $data['image'] = $request->file('image')->store('wheel-prizes', 'public');
            }
        } else {
            if ($wheelPrize->image) {
                Storage::disk('public')->delete($wheelPrize->image);
            }
            $data['image'] = null;
        }

        $data['is_active'] = $request->boolean('is_active', true);

        $wheelPrize->update($data);

        return redirect()->route('admin.wheel-prizes.index')->with('success', 'تم تحديث العنصر بنجاح.');
    }

    public function destroy(WheelPrize $wheelPrize)
    {
        if ($wheelPrize->image) {
            Storage::disk('public')->delete($wheelPrize->image);
        }
        $wheelPrize->delete();
        return redirect()->route('admin.wheel-prizes.index')->with('success', 'تم حذف العنصر.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:wheel_prizes,id',
        ]);

        foreach (array_values($request->ids) as $index => $id) {
            WheelPrize::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function toggle(WheelPrize $wheelPrize)
    {
        $wheelPrize->update(['is_active' => !$wheelPrize->is_active]);
        return redirect()->back()->with('success', 'تم تغيير حالة العنصر.');
    }

    public function inlineSave(Request $request)
    {
        $data = $this->validatePrize($request);

        if ($request->type === 'product' && $request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('wheel-prizes', 'public');
        }

        if ($request->filled('id')) {
            $prize = WheelPrize::findOrFail($request->id);
            if ($request->type !== 'product' && $prize->image) {
                Storage::disk('public')->delete($prize->image);
                $data['image'] = null;
            }
            if ($request->type === 'product' && $request->remove_image && $prize->image) {
                Storage::disk('public')->delete($prize->image);
                $data['image'] = null;
            }
            $prize->update($data);
            return response()->json(['success' => true, 'prize' => $prize->fresh()]);
        }

        $maxSort = WheelPrize::max('sort_order') ?? 0;
        $data['sort_order'] = $maxSort + 1;
        $data['is_active'] = true;

        $prize = WheelPrize::create($data);
        return response()->json(['success' => true, 'prize' => $prize]);
    }

    public function inlineDelete(Request $request)
    {
        $prize = WheelPrize::findOrFail($request->id);
        if ($prize->image) {
            Storage::disk('public')->delete($prize->image);
        }
        $prize->delete();
        return response()->json(['success' => true]);
    }

    private function validatePrize(Request $request, ?WheelPrize $prize = null): array
    {
        $rules = [
            'type' => 'required|in:product,discount',
            'color' => 'required|string|max:20',
            'is_active' => 'boolean',
            'value' => 'nullable|string|max:255',
        ];

        $rules['weight'] = 'nullable|integer|min:1|max:10000';

        if ($request->type === 'product') {
            $rules['name'] = 'required|string|max:255';
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048';
            $rules['discount_percent'] = 'nullable|integer|min:1|max:100';
            $rules['remove_image'] = 'nullable|string';
        } else {
            $rules['name'] = 'nullable|string|max:255';
            $rules['discount_percent'] = 'required|integer|min:1|max:100';
        }

        return $request->validate($rules);
    }
}
