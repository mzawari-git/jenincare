<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'brand',
        'category',
        'skin_type',
        'concerns',
        'ingredients',
        'price',
        'currency',
        'image_path',
        'image_url',
        'affiliate_url',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'concerns' => 'array',
        'ingredients' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];

    public function skinAnalyses(): BelongsToMany
    {
        return $this->belongsToMany(SkinAnalysis::class, 'skin_analysis_products')
            ->withPivot('matching_reason')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeAvailable($query)
    {
        return $query->active()->inStock();
    }

    public function scopeForDefect($query, string $defectType)
    {
        $defectCategoryMap = [
            'acne' => ['cleanser', 'treatment'],
            'pigmentation' => ['serum', 'treatment', 'sunscreen'],
            'dark_circles' => ['eye_care', 'treatment'],
            'dryness' => ['moisturizer', 'mask', 'serum'],
            'oiliness' => ['cleanser', 'toner', 'moisturizer'],
            'pores' => ['toner', 'cleanser', 'exfoliator'],
            'wrinkles' => ['treatment', 'serum', 'moisturizer'],
            'redness' => ['treatment', 'moisturizer'],
            'texture' => ['exfoliator', 'serum', 'treatment'],
            'elasticity' => ['treatment', 'serum', 'supplement'],
        ];

        $categories = $defectCategoryMap[$defectType] ?? ['treatment', 'serum', 'moisturizer'];

        return $query->whereIn('category', $categories);
    }

    public function scopeForSkinType($query, string $skinType)
    {
        return $query->where('skin_type', $skinType);
    }

    public function scopeForConcerns($query, array $concerns)
    {
        return $query->where(function ($q) use ($concerns) {
            foreach ($concerns as $concern) {
                $q->orWhereJsonContains('concerns', $concern);
            }
        });
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && ! empty($this->name_ar)) {
            return $this->name_ar;
        }

        return $this->name;
    }

    public function getDisplayDescriptionAttribute(): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ar' && ! empty($this->description_ar)) {
            return $this->description_ar;
        }

        return $this->description ?? '';
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        $currency = $this->currency ?? 'ILS';

        return number_format((float) $this->price, 2).' '.$currency;
    }

    public function getStockLabelAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'نفذت الكمية';
        }

        if ($this->stock <= 5) {
            return "كمية محدودة: {$this->stock} قطع";
        }

        return 'متوفر';
    }
}
