<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Str;
use DB;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    private $imported = 0;
    private $skipped = 0;
    private $updated = 0;
    private $errors = [];
    private $duplicates = [];
    private $existingNames = null;
    private $existingSkus = null;
    private $headersLogged = false;

    public function model(array $row)
    {
        if (!$this->headersLogged) {
            $this->headersLogged = true;
            $keys = array_keys($row);
            $this->errors[] = "رؤوس الأعمدة: " . implode('، ', array_slice($keys, 0, 20));
        }

        $nameAr = $this->pick($row, [
            'product_name_ar', 'product_name', 'name_ar', 'name',
        ]);

        if (empty($nameAr)) {
            $nameAr = $this->fuzzyFindName($row);
        }

        $nameAr = trim((string) $nameAr);

        if (empty($nameAr)) {
            $this->skipped++;
            if ($this->skipped <= 10) {
                $sample = [];
                foreach (array_slice($row, 0, 8, true) as $k => $v) {
                    $val = is_string($v) ? mb_substr(trim($v), 0, 30) : json_encode($v);
                    if (!empty($val) && $val !== '?') $sample[] = "$k=$val";
                }
                $this->errors[] = "صف $this->skipped: " . (empty($sample) ? "فارغ تماماً" : implode(' | ', $sample));
            }
            return null;
        }

        $nameArMaxLen = 255;
        if (mb_strlen($nameAr) > $nameArMaxLen) {
            $original = $nameAr;
            $nameAr = mb_substr($nameAr, 0, $nameArMaxLen);
            $this->errors[] = "اسم طويل تم قصه: " . mb_substr($original, 0, 40) . "...";
        }

        if (mb_strlen($nameAr) > 100) {
            $possibleDesc = $nameAr;
            $nameAr = mb_substr($nameAr, 0, 100);
            $this->errors[] = "تنبيه: الاسم طويل جداً (قد يكون وصفاً) - تم قصه إلى 100 حرف";
        }

        if ($this->existingNames === null) {
            $this->existingNames = Product::pluck('name_ar')->map(fn($n) => trim($n))->toArray();
            $this->existingSkus = Product::whereNotNull('sku')->pluck('sku')->toArray();
        }

        $barcode = trim((string) $this->pick($row, [
            'product_barcode', 'barcode', 'sku',
        ]));
        $sku = trim((string) $this->pick($row, ['product_sku', 'sku']));

        if (empty($sku) && !empty($barcode) && str_starts_with($barcode, 'PRD-')) {
            $sku = $barcode;
            $barcode = null;
        }
        if (empty($sku) && !empty($barcode) && !str_starts_with($barcode, 'PRD-')) {
            $sku = 'BC-' . $barcode;
        }
        if (empty($sku)) {
            $sku = 'PRD-' . strtoupper(Str::random(7));
        }

        if (Product::where('sku', $sku)->exists() || in_array($sku, $this->existingSkus)) {
            $sku = $sku . '-' . rand(100, 999);
            while (Product::where('sku', $sku)->exists() || in_array($sku, $this->existingSkus)) {
                $sku = 'PRD-' . strtoupper(Str::random(8));
            }
        }

        if ($sku && in_array($sku, $this->existingSkus)) {
            $this->skipped++;
            $this->errors[] = "تخطي: SKU '$sku' موجود مسبقاً";
            return null;
        }

        if (in_array($nameAr, $this->existingNames)) {
            $this->skipped++;
            $this->errors[] = "تخطي: '$nameAr' موجود مسبقاً";
            return null;
        }

        $nameEn = trim((string) $this->pick($row, [
            'product_name_en', 'name_en',
        ]));

        if (empty($nameEn) && !empty($row['product_name'])) {
            $candidate = trim((string) $row['product_name']);
            if (mb_strlen($candidate) > 2 && !preg_match('/[\x{0600}-\x{06FF}]/u', $candidate)) {
                $nameEn = $candidate;
            }
        }

        $categoryId = null;
        $catName = trim((string) $this->pick($row, [
            'category', 'category_name',
        ]));
        if (!empty($catName)) {
            $cat = Category::where('name_ar', 'LIKE', trim($catName))
                ->orWhere('name_en', 'LIKE', trim($catName))
                ->orWhere('slug', Str::slug(trim($catName)))
                ->first();
            if (!$cat) {
                $cat = Category::where('name_ar', 'LIKE', '%' . trim($catName) . '%')->first();
            }
            if ($cat) {
                $categoryId = $cat->id;
            } else {
                $catSlug = Str::slug($catName);
                if (Category::where('slug', $catSlug)->exists()) {
                    $catSlug = $catSlug . '-' . rand(100, 999);
                }
                $cat = Category::create([
                    'name_ar' => $catName,
                    'name_en' => $catName,
                    'slug' => $catSlug,
                    'is_active' => true,
                    'tenant_id' => 1,
                ]);
                $categoryId = $cat->id;
            }
        }

        $brandId = null;
        $brandName = trim((string) $this->pick($row, ['brand', 'brand_name']));
        if (!empty($brandName)) {
            $brand = Brand::where('name_ar', $brandName)
                ->orWhere('name_en', $brandName)
                ->orWhere('slug', Str::slug($brandName))
                ->first();
            if ($brand) $brandId = $brand->id;
        }

        $b2cPrice = $this->parsePrice($this->pick($row, [
            'product_price_1', 'b2c_price', 'price',
        ]));

        $b2bPrice = $this->parsePrice($this->pick($row, [
            'product_price_2', 'product_price_3', 'b2b_price',
        ]));

        $costPrice = $this->parsePrice($this->pick($row, ['product_cost', 'cost_price']));

        $stockQty = intval($this->pick($row, [
            'product_stock', 'stock_quantity', 'stock',
        ]) ?? 0);

        $descAr = trim((string) $this->pick($row, [
            'product_description', 'description_ar', 'description',
        ]));

        $descEn = trim((string) $this->pick($row, ['description_en']));

        $imageUrl = trim((string) $this->pick($row, [
            'product_image_url_1', 'product_image', 'main_image', 'image_url', 'image',
        ]));

        if (empty($imageUrl) || $imageUrl === '?' || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $imageUrl = $this->fuzzyFindImage($row, 1);
        }

        $galleryUrls = [];
        foreach (['product_image_url_2', 'product_image_url_3', 'product_image_url_4', 'product_image_url_5'] as $key) {
            $val = trim((string) ($row[$key] ?? ''));
            if (!empty($val) && $val !== '?' && filter_var($val, FILTER_VALIDATE_URL)) {
                $galleryUrls[] = $val;
            }
        }

        if (empty($galleryUrls)) {
            for ($i = 2; $i <= 10; $i++) {
                $img = $this->fuzzyFindImage($row, $i);
                if ($img && !in_array($img, $galleryUrls) && $img !== $imageUrl) {
                    $galleryUrls[] = $img;
                }
                if (count($galleryUrls) >= 4) break;
            }
        }

        $discountPct = floatval($this->pick($row, ['discount_percentage', 'discount']) ?? 0);

        $rawStatus = trim((string) $this->pick($row, ['product_is_active', 'status', 'is_active']));
        $status = $this->resolveStatus($rawStatus);

        $isFeatured = $this->parseBool($this->pick($row, ['is_featured', 'featured']));
        $isNew = $this->parseBool($this->pick($row, ['is_new', 'new']));

        $weight = trim((string) $this->pick($row, ['weight', 'product_weight']));

        $deliveryDays = intval($this->pick($row, ['estimated_delivery_days', 'delivery_days']) ?? 3);

        $slugBase = Str::slug($nameAr);
        $slug = $slugBase;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $counter;
            $counter++;
        }

        if (!empty($sku)) {
            while (Product::where('sku', $sku)->exists()) {
                $sku = $sku . '-' . rand(100, 999);
            }
        } else {
            $sku = 'PRD-' . strtoupper(Str::random(6));
            while (Product::where('sku', $sku)->exists()) {
                $sku = 'PRD-' . strtoupper(Str::random(6));
            }
        }

        $stockStatus = $stockQty > 10 ? 'in_stock' : ($stockQty > 0 ? 'low_stock' : 'out_of_stock');

        if (Product::where('sku', $sku)->exists() || in_array($sku, $this->existingSkus)) {
            $this->skipped++;
            $catName2 = trim((string) $this->pick($row, ['category', 'category_name']));
            $this->duplicates[] = ['name' => $nameAr, 'sku' => $sku, 'barcode' => $barcode, 'category' => $catName2];
            return null;
        }
        if (Product::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . rand(100, 999);
            while (Product::where('slug', $slug)->exists()) $slug = $slugBase . '-' . rand(1000, 9999);
        }

        $this->imported++;
        $this->existingNames[] = $nameAr;
        if ($sku) $this->existingSkus[] = $sku;

        $this->fixSoftDeletedConflict('sku', $sku);
        if (Product::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . rand(100, 999);
            while (Product::where('slug', $slug)->exists()) $slug = $slugBase . '-' . rand(1000, 9999);
        }
        $this->fixSoftDeletedConflict('slug', $slug);

        return new Product([
            'tenant_id' => 1,
            'name_ar' => $nameAr,
            'name_en' => $nameEn ?: null,
            'slug' => $slug,
            'sku' => $sku,
            'barcode' => $barcode ?: null,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'description_ar' => $descAr ?: null,
            'description_en' => $descEn ?: null,
            'b2c_price' => round($b2cPrice, 2),
            'b2b_price' => $b2bPrice ? round($b2bPrice, 2) : round($b2cPrice * 0.8, 2),
            'cost_price' => $costPrice ? round($costPrice, 2) : null,
            'discount_percentage' => min(100, max(0, $discountPct)),
            'stock_quantity' => $stockQty,
            'stock_status' => $stockStatus,
            'main_image' => $imageUrl ?: null,
            'gallery_images' => !empty($galleryUrls) ? json_encode($galleryUrls) : null,
            'meta_description' => $descAr ? Str::limit(strip_tags($descAr), 160) : null,
            'status' => $status,
            'is_featured' => $isFeatured,
            'is_new' => $isNew,
            'show_in_b2c' => true,
            'show_in_b2b' => true,
            'weight' => $weight ?: null,
            'estimated_delivery_days' => $deliveryDays ?: 3,
            'base_price' => $b2cPrice,
            'published_at' => $status === 'active' ? now() : null,
        ]);
    }

    public function rules(): array
    {
        return [];
    }

    public function getImportedCount(): int { return $this->imported; }
    public function getSkippedCount(): int { return $this->skipped; }
    public function getUpdatedCount(): int { return $this->updated; }
    public function getErrors(): array { return $this->errors; }
    public function getDuplicates(): array { return $this->duplicates; }

    private function pick(array $row, array $keys)
    {
        foreach ($keys as $key) {
            $normalized = str_replace([' ', '-'], '_', strtolower(trim($key)));
            foreach ($row as $rk => $rv) {
                if (str_replace([' ', '-'], '_', strtolower(trim($rk))) === $normalized) {
                    return $rv;
                }
            }
            if (array_key_exists($key, $row)) return $row[$key];
        }
        return null;
    }

    private function resolveStatus(string $value): string
    {
        $v = strtolower(trim($value));

        if (in_array($v, ['1', 'true', 'yes', 'active', 'نشط', 'مفعل', 'فعال'])) {
            return 'active';
        }
        if (in_array($v, ['0', 'false', 'no', 'inactive', 'غير_نشط', 'معطل'])) {
            return 'inactive';
        }
        if (in_array($v, ['draft', 'مسودة'])) {
            return 'draft';
        }

        return 'active';
    }

    private function parsePrice($value): float
    {
        if ($value === null || $value === '') return 0;
        if (is_numeric($value)) return max(0, floatval($value));
        $cleaned = preg_replace('/[^0-9.]/', '', (string) $value);
        return is_numeric($cleaned) ? max(0, floatval($cleaned)) : 0;
    }

    private function fuzzyFindImage(array $row, int $offset = 1): ?string
    {
        $found = 0;
        $imgKeywords = ['image', 'img', 'صورة', 'صوره', 'photo', 'url', 'رابط'];

        foreach ($row as $key => $value) {
            if (empty($value) || !is_string($value)) continue;
            $v = trim($value);
            if (!filter_var($v, FILTER_VALIDATE_URL)) continue;
            if (!str_contains($v, '.') && !str_contains($v, 'http')) continue;

            $keyLower = str_replace([' ', '-', '_'], '', strtolower(trim($key)));
            foreach ($imgKeywords as $kw) {
                if (str_contains($keyLower, str_replace(' ', '', $kw))) {
                    $found++;
                    if ($found === $offset) return $v;
                    break;
                }
            }
        }

        return null;
    }

    private function parseBool($value): bool
    {
        if ($value === null || $value === '') return false;
        if (is_bool($value)) return $value;
        $v = strtolower(trim((string) $value));
        return in_array($v, ['1', 'true', 'yes', 'نعم', 'صح', 'مفعل', 'نشط', 'active']);
    }

    private function fixSoftDeletedConflict(string $field, string $value): void
    {
        $conflict = Product::withTrashed()->where($field, $value)->whereNotNull('deleted_at')->first();
        if ($conflict) {
            $conflict->forceDelete();
        }
    }

    private function fuzzyFindName(array $row): ?string
    {
        $nameKeywords = ['name', 'اسم', 'منتج', 'product', 'عنوان', 'title', 'label', 'صنف', 'بند', 'item', 'goods'];
        $skipKeywords = ['image', 'url', 'price', 'سعر', 'photo', 'صورة', 'category', 'فئة', 'code', 'barcode', 'stock', 'quantity', 'كمية', 'مخزون', 'status', 'حالة', 'active', 'description', 'وصف', 'cost', 'تكلفة', 'discount', 'خصم', 'brand', 'ماركة', 'weight', 'وزن', 'slug', 'id', 'sku'];

        $bestKey = null;
        $bestScore = 0;

        foreach ($row as $key => $value) {
            if (empty($value) || !is_string($value)) continue;
            $v = trim($value);
            if (mb_strlen($v) < 2) continue;
            if (is_numeric($v)) continue;
            if (filter_var($v, FILTER_VALIDATE_URL)) continue;

            $keyLower = str_replace([' ', '-', '_'], '', strtolower(trim($key)));
            $score = 0;
            foreach ($nameKeywords as $kw) {
                if (str_contains($keyLower, str_replace(' ', '', $kw))) $score += 10;
            }
            foreach ($skipKeywords as $kw) {
                if (str_contains($keyLower, str_replace(' ', '', $kw))) $score -= 5;
            }
            if ($score > $bestScore) { $bestScore = $score; $bestKey = $key; }
        }

        if ($bestKey && $bestScore > 0) return $row[$bestKey];

        foreach ($row as $key => $value) {
            if (empty($value) || !is_string($value)) continue;
            $v = trim($value);
            if (mb_strlen($v) >= 3 && mb_strlen($v) <= 150 && !is_numeric($v) && !filter_var($v, FILTER_VALIDATE_URL)) {
                if (preg_match('/[\x{0600}-\x{06FF}]/u', $v)) return $v;
            }
        }

        foreach ($row as $key => $value) {
            if (empty($value) || !is_string($value)) continue;
            $v = trim($value);
            $keyLower = str_replace([' ', '-', '_'], '', strtolower(trim($key)));
            $skipIt = false;
            foreach ($skipKeywords as $kw) {
                if (str_contains($keyLower, str_replace(' ', '', $kw))) { $skipIt = true; break; }
            }
            if (!$skipIt && mb_strlen($v) >= 3 && mb_strlen($v) <= 150 && !is_numeric($v) && !filter_var($v, FILTER_VALIDATE_URL)) {
                return $v;
            }
        }

        return null;
    }
}
