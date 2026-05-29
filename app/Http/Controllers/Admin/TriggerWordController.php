<?php

namespace App\Http\Controllers\Admin;

use App\Models\TriggerWord;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TriggerWordController extends Controller
{
    public function index(Request $request)
    {
        $query = TriggerWord::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('search')) {
            $query->where('word', 'like', '%' . $request->search . '%');
        }

        $words = $query->orderBy('category')->orderBy('word')->paginate(50);
        $categories = TriggerWord::select('category')->distinct()->pluck('category');
        $platforms = TriggerWord::select('platform')->distinct()->whereNotNull('platform')->pluck('platform');

        return view('admin.trigger-words.index', compact('words', 'categories', 'platforms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'word' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'severity' => 'required|in:low,medium,high,critical',
            'platform' => 'nullable|string|max:50',
            'action' => 'required|in:remove,replace,block',
            'replacement' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        TriggerWord::create($data);

        return redirect()->route('admin.trigger-words.index')
            ->with('success', 'تمت إضافة الكلمة بنجاح');
    }

    public function update(Request $request, TriggerWord $triggerWord)
    {
        $data = $request->validate([
            'word' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'severity' => 'required|in:low,medium,high,critical',
            'platform' => 'nullable|string|max:50',
            'action' => 'required|in:remove,replace,block',
            'replacement' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $triggerWord->update($data);

        return redirect()->route('admin.trigger-words.index')
            ->with('success', 'تم تحديث الكلمة بنجاح');
    }

    public function destroy(TriggerWord $triggerWord)
    {
        $triggerWord->delete();

        return redirect()->route('admin.trigger-words.index')
            ->with('success', 'تم حذف الكلمة بنجاح');
    }

    public function toggle(TriggerWord $triggerWord)
    {
        $triggerWord->update(['active' => !$triggerWord->active]);

        return response()->json(['success' => true, 'active' => $triggerWord->active]);
    }

    public function import(Request $request)
    {
        $request->validate(['words' => 'required|array']);
        $count = 0;

        foreach ($request->words as $wordData) {
            TriggerWord::updateOrCreate(
                ['word' => $wordData['word'], 'platform' => $wordData['platform'] ?? null],
                $wordData
            );
            $count++;
        }

        return redirect()->route('admin.trigger-words.index')
            ->with('success', "تم استيراد {$count} كلمة بنجاح");
    }
}
