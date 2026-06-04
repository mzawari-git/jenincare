<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class GenerateBulkSeo extends Command
{
    protected $signature = 'seo:generate-bulk
                            {--limit= : عدد المنتجات المراد معالجتها (افتراضي: الكل)}
                            {--missing-only : معالجة المنتجات التي تفتقد SEO فقط}
                            {--category= : معالجة منتجات قسم معين}
                            {--dry-run : عرض النتائج دون حفظها}';

    protected $description = 'توليد SEO Metadata تلقائي بالذكاء الاصطناعي لجميع المنتجات';

    private array $categoryKeywords = [
        'شامبو' => ['شامبو', 'عناية بالشعر', 'تنظيف الشعر', 'منتجات شعر', 'شعر صحي'],
        'بلسم' => ['بلسم', 'ترطيب الشعر', 'تنعيم الشعر', 'عناية بالشعر'],
        'زيت' => ['زيت طبيعي', 'عناية بالبشرة', 'ترطيب', 'تغذية البشرة'],
        'ماسك' => ['ماسك', 'مقشر', 'عناية بالبشرة', 'تجديد البشرة'],
        'سبراي' => ['سبراي', 'بخاخ', 'تثبيت المكياج', 'عناية يومية'],
        'جل' => ['جل', 'ترطيب', 'تثبيت', 'عناية بالبشرة'],
        'كريم' => ['كريم', 'ترطيب', 'مضاد تجاعيد', 'عناية بالبشرة'],
        'مكياج' => ['مكياج', 'تجميل', 'أدوات تجميل', 'جمال'],
        'أحمر شفاه' => ['أحمر شفاه', 'مكياج', 'تجميل', 'شفاه'],
        'عطور' => ['عطر', 'بخور', 'عطور أصلية', 'فلسطين'],
        'أجهزة' => ['أجهزة العناية', 'تقنية متقدمة', 'عناية احترافية', 'أجهزة تجميل'],
        'صالون' => ['تجهيز صالونات', 'معدات صالون', 'أثاث صالون', 'تجميل احترافي'],
    ];

    private array $beautyTemplates = [
        "{product} من {brand} — {category} أصلي 100% بجودة عالمية. اشتري الآن من جنين للتجميل مع شحن سريع لكل فلسطين.",
        "اكتشفي {product} بأفضل سعر في فلسطين. منتج أصلي من {brand} ضمن قسم {category} مع ضمان الجودة والأصالة.",
        "{product}: الحل المثالي لروتين العناية اليومي. متوفر الآن في جنين للتجميل بتوصيل سريع ودفع عند الاستلام.",
        "تسوقي {product} الأصلي من {brand} — {category} احترافي يضمن لكِ نتائج مبهرة. شحن لكل فلسطين.",
        "{product} ضمن تشكيلة {category} المميزة. منتجات أصلية 100% بأسعار تنافسية وخدمة عملاء احترافية.",
    ];

    public function handle()
    {
        $query = Product::with('category', 'brand');

        if ($this->option('missing-only')) {
            $query->where(function ($q) {
                $q->whereNull('meta_title')
                  ->orWhere('meta_title', '')
                  ->orWhereNull('meta_description')
                  ->orWhere('meta_description', '');
            });
        }

        if ($this->option('category')) {
            $category = Category::where('name_ar', 'like', '%' . $this->option('category') . '%')
                ->orWhere('slug', $this->option('category'))
                ->first();
            if ($category) {
                $query->where('category_id', $category->id);
            } else {
                $this->error('القسم المطلوب غير موجود!');
                return 1;
            }
        }

        if ($this->option('limit')) {
            $query->limit((int) $this->option('limit'));
        }

        $products = $query->get();
        $count = $products->count();

        if ($count === 0) {
            $this->warn('لا توجد منتجات للمعالجة.');
            return 0;
        }

        $this->info("جاري معالجة {$count} منتج...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $updated = 0;
        $skipped = 0;

        foreach ($products as $product) {
            $seo = $this->generateSmartSeo($product);

            if ($this->option('dry-run')) {
                $this->newLine();
                $this->info("المنتج: {$product->name_ar}");
                $this->line("  العنوان: {$seo['meta_title']}");
                $this->line("  الوصف: {$seo['meta_description']}");
                $this->line("  الكلمات: " . implode(', ', $seo['meta_keywords']));
            } else {
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
                    $updated++;
                } else {
                    $skipped++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info("اكتملت المحاكاة — لم يتم حفظ أي تغييرات.");
        } else {
            $this->info("اكتمل! تم تحديث {$updated} منتج، تم تخطي {$skipped} منتج (SEO موجود مسبقاً).");
        }

        return 0;
    }

    private function generateSmartSeo(Product $product): array
    {
        $name = $product->name_ar;
        $categoryName = $product->category?->name_ar ?? 'منتجات التجميل';
        $brandName = $product->brand?->name ?? 'ماركة عالمية';
        $cleanDesc = strip_tags($product->description_ar ?? '');

        // Generate meta title (max 60 chars optimized)
        $title = $this->generateTitle($name, $categoryName, $brandName);

        // Generate meta description (max 160 chars optimized)
        $description = $this->generateDescription($name, $categoryName, $brandName, $cleanDesc);

        // Generate keywords using smart extraction
        $keywords = $this->generateKeywords($name, $categoryName, $brandName, $cleanDesc);

        return [
            'meta_title' => $title,
            'meta_description' => $description,
            'meta_keywords' => $keywords,
        ];
    }

    private function generateTitle(string $name, string $category, string $brand): string
    {
        $base = "{$name} — {$category} أصلي | جنين للتجميل";
        if (mb_strlen($base) > 60) {
            $base = Str::limit($name, 40) . ' | جنين للتجميل';
        }
        return $base;
    }

    private function generateDescription(string $name, string $category, string $brand, string $desc): string
    {
        $template = $this->beautyTemplates[array_rand($this->beautyTemplates)];
        $description = str_replace(
            ['{product}', '{brand}', '{category}'],
            [$name, $brand, $category],
            $template
        );

        // If description is too short, supplement with product description
        if (mb_strlen($description) < 100 && !empty($desc)) {
            $description .= ' ' . Str::limit($desc, 80);
        }

        return Str::limit($description, 160);
    }

    private function generateKeywords(string $name, string $category, string $brand, string $desc): array
    {
        $keywords = [];

        // Add product name words
        $nameWords = explode(' ', $name);
        foreach ($nameWords as $word) {
            $word = trim($word);
            if (mb_strlen($word) > 2 && !in_array($word, $keywords)) {
                $keywords[] = $word;
            }
        }

        // Add category
        if (!in_array($category, $keywords)) {
            $keywords[] = $category;
        }

        // Add brand
        if (!in_array($brand, $keywords)) {
            $keywords[] = $brand;
        }

        // Add category-specific keywords
        foreach ($this->categoryKeywords as $catKey => $catWords) {
            if (str_contains($category, $catKey) || str_contains($name, $catKey)) {
                foreach ($catWords as $cw) {
                    if (!in_array($cw, $keywords)) {
                        $keywords[] = $cw;
                    }
                }
            }
        }

        // Extract keywords from description
        if (!empty($desc)) {
            $descWords = explode(' ', $desc);
            foreach ($descWords as $word) {
                $word = trim($word);
                if (mb_strlen($word) > 3 && mb_strlen($word) < 20 && !in_array($word, $keywords)) {
                    $keywords[] = $word;
                    if (count($keywords) >= 15) break;
                }
            }
        }

        // Add global keywords
        $globalKeywords = ['جنين للتجميل', 'JeninCare', 'منتجات أصلية', 'شحن فلسطين', 'عناية بالبشرة', 'تجميل'];
        foreach ($globalKeywords as $gk) {
            if (!in_array($gk, $keywords)) {
                $keywords[] = $gk;
            }
        }

        return array_slice($keywords, 0, 20);
    }
}
