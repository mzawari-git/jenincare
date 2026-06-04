<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ar', 'like', "%{$request->search}%")
                  ->orWhere('name_en', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('filter')) {
            if ($request->filter === 'missing_meta') {
                $query->where(function ($q) {
                    $q->whereNull('meta_title')->orWhere('meta_title', '')->orWhere('meta_description', '')->orWhereNull('meta_description');
                });
            } elseif ($request->filter === 'missing_keywords') {
                $query->whereNull('meta_keywords');
            } elseif ($request->filter === 'missing_og') {
                $query->whereNull('og_image')->orWhere('og_image', '');
            } elseif ($request->filter === 'seo_ready') {
                $query->whereNotNull('meta_title')->where('meta_title', '!=', '')
                      ->whereNotNull('meta_description')->where('meta_description', '!=', '')
                      ->whereNotNull('meta_keywords');
            }
        }

        $products = $query->paginate(30);

        foreach ($products as $product) {
            $product->seo_score = $this->calculateSeoScore($product);
        }

        $stats = [
            'total' => Product::count(),
            'seo_ready' => Product::whereNotNull('meta_title')->where('meta_title', '!=', '')->whereNotNull('meta_description')->where('meta_description', '!=', '')->whereNotNull('meta_keywords')->count(),
            'missing_meta' => Product::where(function ($q) { $q->whereNull('meta_title')->orWhere('meta_title', '')->orWhere('meta_description', '')->orWhereNull('meta_description'); })->count(),
            'missing_keywords' => Product::whereNull('meta_keywords')->count(),
            'missing_og' => Product::whereNull('og_image')->orWhere('og_image', '')->count(),
        ];

        return view('admin.seo.index', compact('products', 'stats'));
    }

    public function bulkEdit($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return view('admin.seo.edit', compact('product'));
    }

    public function bulkUpdate(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'meta_title' => 'nullable|string|max:160',
            'meta_title_ar' => 'nullable|string|max:160',
            'meta_description' => 'nullable|string|max:320',
            'meta_description_ar' => 'nullable|string|max:320',
            'meta_keywords' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
        ]);

        $product->update($data);

        return redirect()->route('admin.seo.index')->with('success', 'تم تحديث SEO لـ ' . $product->name_ar);
    }

    public function autoGenerate($id)
    {
        $product = Product::findOrFail($id);
        $categoryName = $product->category->name_ar ?? '';

        $product->update([
            'meta_title' => $product->meta_title ?: ($product->name_ar . ' - ' . $categoryName . ' | JeninCare'),
            'meta_description' => $product->meta_description ?: \Str::limit(strip_tags($product->description_ar ?? $product->name_ar), 160),
            'meta_keywords' => $product->meta_keywords ?: json_encode($this->generateKeywords($product)),
        ]);

        return redirect()->route('admin.seo.index')->with('success', 'تم إنشاء SEO تلقائياً لـ ' . $product->name_ar);
    }

    public function autoGenerateAll()
    {
        $products = Product::whereNull('meta_title')->orWhere('meta_title', '')->orWhereNull('meta_description')->orWhere('meta_description', '')->get();

        $count = 0;
        foreach ($products as $product) {
            $categoryName = $product->category->name_ar ?? '';
            $product->update([
                'meta_title' => $product->meta_title ?: ($product->name_ar . ' - ' . $categoryName . ' | JeninCare'),
                'meta_description' => $product->meta_description ?: \Str::limit(strip_tags($product->description_ar ?? $product->name_ar), 160),
                'meta_keywords' => $product->meta_keywords ?: json_encode($this->generateKeywords($product)),
            ]);
            $count++;
        }

        return redirect()->route('admin.seo.index')->with('success', "تم إنشاء SEO تلقائياً لـ {$count} منتج");
    }

    public function aiGenerateAll(Request $request)
    {
        $limit = $request->input('limit', 100);
        $missingOnly = $request->boolean('missing_only', true);

        $query = Product::with('category', 'brand');

        if ($missingOnly) {
            $query->where(function ($q) {
                $q->whereNull('meta_title')
                  ->orWhere('meta_title', '')
                  ->orWhereNull('meta_description')
                  ->orWhere('meta_description', '');
            });
        }

        $products = $query->limit($limit)->get();
        $count = 0;

        foreach ($products as $product) {
            $seo = $this->generateAiSeo($product);
            $updateData = [];

            if (empty($product->meta_title)) {
                $updateData['meta_title'] = $seo['meta_title'];
            }
            if (empty($product->meta_description)) {
                $updateData['meta_description'] = $seo['meta_description'];
            }
            if (empty($product->meta_keywords)) {
                $updateData['meta_keywords'] = json_encode($seo['meta_keywords']);
            }
            if (empty($product->og_image) && $product->main_image_url) {
                $updateData['og_image'] = $product->main_image_url;
            }

            if (!empty($updateData)) {
                $product->update($updateData);
                $count++;
            }
        }

        return redirect()->route('admin.seo.index')->with('success', "تم إنشاء SEO ذكي لـ {$count} منتج من أصل {$products->count()}");
    }

    private function generateAiSeo(Product $product): array
    {
        $name = $product->name_ar;
        $category = $product->category?->name_ar ?? 'منتجات التجميل';
        $brand = $product->brand?->name ?? 'ماركة عالمية';
        $cleanDesc = strip_tags($product->description_ar ?? '');

        // AI-optimized title with emotional triggers and keywords
        $title = $this->buildAiTitle($name, $category, $brand);

        // AI-optimized description with value proposition and local SEO
        $description = $this->buildAiDescription($name, $category, $brand, $cleanDesc);

        // Smart keyword extraction with semantic grouping
        $keywords = $this->buildAiKeywords($name, $category, $brand, $cleanDesc);

        return [
            'meta_title' => $title,
            'meta_description' => $description,
            'meta_keywords' => $keywords,
        ];
    }

    private function buildAiTitle(string $name, string $category, string $brand): string
    {
        $templates = [
            "{$name} من {$brand} — {$category} أصلي | جنين للتجميل",
            "{$name}: {$category} احترافي بأفضل سعر | جنين للتجميل",
            "اشتري {$name} — {$category} أصلي 100% | توصيل فلسطين",
        ];
        $title = $templates[array_rand($templates)];
        return \Str::limit($title, 60);
    }

    private function buildAiDescription(string $name, string $category, string $brand, string $desc): string
    {
        $valueProps = [
            "منتج أصلي 100% بضمان الجودة. شحن سريع لكل فلسطين ودفع عند الاستلام.",
            "احصلي على أفضل سعر في فلسطين. توصيل مجاني للطلبات الكبيرة ودعم احترافي.",
            "مستحضرات أصلية من مصادر موثوقة. اطلبي الآن واستلمي خلال 24-48 ساعة.",
        ];
        $valueProp = $valueProps[array_rand($valueProps)];

        $base = "{$name} من {$brand} ضمن تشكيلة {$category} المتميزة في جنين للتجميل. ";
        if (!empty($desc)) {
            $base .= \Str::limit($desc, 80) . ' ';
        }
        $base .= $valueProp;

        return \Str::limit($base, 160);
    }

    private function buildAiKeywords(string $name, string $category, string $brand, string $desc): array
    {
        $keywords = [];

        // Product name decomposition
        $nameWords = array_filter(explode(' ', $name), fn($w) => mb_strlen($w) > 2);
        $keywords = array_merge($keywords, array_values($nameWords));

        // Category synonyms
        $catSynonyms = [
            'شامبو' => ['شامبو', 'غسول شعر', 'عناية بالشعر'],
            'بلسم' => ['بلسم', 'مرطب شعر', 'تنعيم'],
            'كريم' => ['كريم', 'ترطيب', 'عناية بالبشرة'],
            'مكياج' => ['مكياج', 'تجميل', 'أدوات تجميل'],
            'عطر' => ['عطر', 'عطور', 'بخور'],
            'جهاز' => ['أجهزة تجميل', 'تقنية متقدمة', 'عناية احترافية'],
        ];

        foreach ($catSynonyms as $key => $synonyms) {
            if (str_contains($category, $key) || str_contains($name, $key)) {
                $keywords = array_merge($keywords, $synonyms);
            }
        }

        // Add core terms
        $keywords[] = $category;
        $keywords[] = $brand;
        $keywords[] = 'جنين للتجميل';
        $keywords[] = 'JeninCare';
        $keywords[] = 'منتجات أصلية';
        $keywords[] = 'شحن فلسطين';
        $keywords[] = 'دفع عند الاستلام';

        // Extract from description
        if (!empty($desc)) {
            $descWords = array_filter(explode(' ', $desc), fn($w) => mb_strlen($w) > 3 && mb_strlen($w) < 20);
            $keywords = array_merge($keywords, array_slice(array_values($descWords), 0, 5));
        }

        return array_slice(array_unique($keywords), 0, 20);
    }

    private function calculateSeoScore($product)
    {
        $score = 0;
        if (!empty($product->meta_title)) $score += 25;
        if (!empty($product->meta_description)) $score += 25;
        if (!empty($product->meta_keywords)) $score += 20;
        if (!empty($product->og_image)) $score += 15;
        if (!empty($product->slug)) $score += 15;
        return $score;
    }

    private function generateKeywords($product)
    {
        $keywords = [];
        $nameWords = explode(' ', $product->name_ar);
        foreach ($nameWords as $w) {
            if (mb_strlen($w) > 2) $keywords[] = $w;
        }
        if ($product->category) {
            $catWords = explode(' ', $product->category->name_ar ?? '');
            foreach ($catWords as $w) {
                if (mb_strlen($w) > 2) $keywords[] = $w;
            }
        }
        $keywords[] = 'JeninCare';
        $keywords[] = 'تجميل';
        $keywords[] = 'عناية';
        return array_unique($keywords);
    }
}
