<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'sku', 'barcode', 'name_ar', 'name_en', 'description_ar', 'description_en',
        'short_description_ar', 'category_id', 'brand_id', 'tags', 'base_price', 'b2c_price', 'b2b_price',
        'cost_price', 'discount_percentage', 'discount_amount', 'discount_starts_at', 'discount_ends_at',
        'b2b_min_quantity', 'b2b_tier_1_qty', 'b2b_tier_1_price', 'b2b_tier_2_qty', 'b2b_tier_2_price',
        'b2b_tier_3_qty', 'b2b_tier_3_price', 'stock_quantity', 'reserved_quantity', 'low_stock_alert',
        'stock_status', 'track_inventory', 'allow_backorder', 'main_image', 'main_image_webp', 'gallery_images',
        'thumbnail', 'video_url', 'specifications', 'attributes', 'weight', 'dimensions', 'slug', 'meta_title',
        'meta_description', 'meta_keywords', 'og_image', 'average_rating', 'reviews_count', 'views_count',
        'sales_count', 'status', 'is_featured', 'is_new', 'is_bestseller', 'show_in_b2c', 'show_in_b2b',
        'published_at', 'free_shipping', 'shipping_cost', 'estimated_delivery_days', 'compliance_checked',
        'compliance_checked_at', 'compliance_flags', 'safety_warnings', 'barcode_slug', 'print_count', 'last_printed_at'
    ];

    protected $casts = [
        'tags' => 'json',
        'gallery_images' => 'json',
        'specifications' => 'json',
        'attributes' => 'json',
        'dimensions' => 'json',
        'meta_keywords' => 'json',
        'compliance_flags' => 'json',
        'discount_starts_at' => 'datetime',
        'discount_ends_at' => 'datetime',
        'published_at' => 'datetime',
        'last_printed_at' => 'datetime',
        'compliance_checked_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_bestseller' => 'boolean',
        'show_in_b2c' => 'boolean',
        'show_in_b2b' => 'boolean',
        'free_shipping' => 'boolean',
        'allow_backorder' => 'boolean',
        'track_inventory' => 'boolean',
        'compliance_checked' => 'boolean',
    ];

    protected $appends = [
        'available_quantity',
        'final_b2c_price',
        'is_on_sale',
        'discount_percentage_display',
        'stock_status_label',
        'name',
        'description',
        'main_image_url',
        'thumbnail_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
            if (empty($product->slug)) {
                $product->slug = \Illuminate\Support\Str::slug($product->name_ar);
            }
            if (empty($product->barcode_slug)) {
                $product->barcode_slug = 'BC-' . time() . '-' . $product->id;
            }
            if (empty($product->tenant_id)) {
                $product->tenant_id = 1;
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('stock_quantity') || $product->isDirty('reserved_quantity')) {
                $product->updateStockStatus();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_related', 'product_id', 'related_id')
            ->withPivot('sort_order');
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?? $this->name_ar);
    }

    public function getDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : ($this->description_en ?? $this->description_ar);
    }

    public function getMainImageUrlAttribute(): ?string
    {
        if ($this->main_image_webp) {
            return $this->isAbsoluteUrl($this->main_image_webp)
                ? $this->main_image_webp
                : url('files/products/' . $this->main_image_webp);
        }
        if ($this->main_image) {
            return $this->isAbsoluteUrl($this->main_image)
                ? $this->main_image
                : url('files/products/' . $this->main_image);
        }
        return null;
    }

    private function isAbsoluteUrl(string $url): bool
    {
        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail) {
            return url('files/products/thumbnails/' . $this->thumbnail);
        }
        return $this->main_image_url;
    }

    public function optimizedImageUrl(int $width = 600, ?int $height = null, int $quality = 85, bool $webp = true): ?string
    {
        $image = $this->main_image_webp ?: $this->main_image;
        if (!$image) return null;
        if ($this->isAbsoluteUrl($image)) return $image;

        $params = ['w' => $width, 'q' => $quality];
        if ($height) $params['h'] = $height;
        if ($webp) $params['webp'] = 1;

        return route('image.optimized', ['path' => 'products/' . $image]) . '?' . http_build_query($params);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->stock_quantity - $this->reserved_quantity);
    }

    public function getFinalB2cPriceAttribute(): float
    {
        return $this->getCurrentPrice();
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->isDiscountActive();
    }

    public function getDiscountPercentageDisplayAttribute(): float
    {
        if (!$this->isDiscountActive()) {
            return 0;
        }
        if ($this->discount_percentage > 0) {
            return (float) $this->discount_percentage;
        }
        if ($this->discount_amount > 0 && $this->b2c_price > 0) {
            return round(($this->discount_amount / $this->b2c_price) * 100, 2);
        }
        return 0;
    }

    public function getStockStatusLabelAttribute(): string
    {
        $labels = [
            'in_stock' => 'متوفر',
            'low_stock' => 'كمية محدودة',
            'out_of_stock' => 'غير متوفر',
            'pre_order' => 'طلب مسبق',
        ];
        return $labels[$this->stock_status] ?? 'غير معروف';
    }

    public function getStockStatusColorAttribute(): string
    {
        $colors = [
            'in_stock' => 'success',
            'low_stock' => 'warning',
            'out_of_stock' => 'danger',
            'pre_order' => 'info',
        ];
        return $colors[$this->stock_status] ?? 'secondary';
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->final_b2c_price, 2) . ' ₪';
    }

    public function getCurrentPrice(): float
    {
        if ($this->isDiscountActive()) {
            if ($this->discount_percentage > 0) {
                return round($this->b2c_price * (1 - $this->discount_percentage / 100), 2);
            }
            return max(0, $this->b2c_price - $this->discount_amount);
        }
        return (float) $this->b2c_price;
    }

    public function isDiscountActive(): bool
    {
        if ($this->discount_amount <= 0 && $this->discount_percentage <= 0) {
            return false;
        }
        $now = now();
        if ($this->discount_starts_at && $this->discount_starts_at > $now) {
            return false;
        }
        if ($this->discount_ends_at && $this->discount_ends_at < $now) {
            return false;
        }
        return true;
    }

    public function getB2bPriceForQuantity(int $quantity): float
    {
        if ($quantity >= $this->b2b_tier_3_qty && $this->b2b_tier_3_price) {
            return (float) $this->b2b_tier_3_price;
        }
        if ($quantity >= $this->b2b_tier_2_qty && $this->b2b_tier_2_price) {
            return (float) $this->b2b_tier_2_price;
        }
        if ($quantity >= $this->b2b_tier_1_qty && $this->b2b_tier_1_price) {
            return (float) $this->b2b_tier_1_price;
        }
        return (float) $this->b2b_price;
    }

    public function getB2bTierForQuantity(int $quantity): int
    {
        if ($quantity >= $this->b2b_tier_3_qty && $this->b2b_tier_3_price) return 3;
        if ($quantity >= $this->b2b_tier_2_qty && $this->b2b_tier_2_price) return 2;
        if ($quantity >= $this->b2b_tier_1_qty && $this->b2b_tier_1_price) return 1;
        return 0;
    }

    public function isInStock(int $quantity = 1): bool
    {
        if (!$this->track_inventory) return true;
        if ($this->allow_backorder) return true;
        return $this->available_quantity >= $quantity;
    }

    public function canBackorder(): bool
    {
        return $this->allow_backorder && $this->stock_status === 'out_of_stock';
    }

    public function reserveStock(int $quantity): bool
    {
        if (!$this->track_inventory) return true;
        if (!$this->isInStock($quantity)) return false;
        $this->increment('reserved_quantity', $quantity);
        return true;
    }

    public function releaseStock(int $quantity): void
    {
        if (!$this->track_inventory) return;
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }

    public function confirmSale(int $quantity): void
    {
        if (!$this->track_inventory) return;
        $this->decrement('stock_quantity', $quantity);
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
        $this->increment('sales_count');
    }

    public function updateStockStatus(): void
    {
        if (!$this->track_inventory) {
            $this->stock_status = 'in_stock';
            $this->saveQuietly();
            return;
        }
        $available = $this->available_quantity;
        if ($available <= 0) {
            $this->stock_status = 'out_of_stock';
        } elseif ($available <= $this->low_stock_alert) {
            $this->stock_status = 'low_stock';
        } else {
            $this->stock_status = 'in_stock';
        }
        $this->saveQuietly();
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function updateRating(): void
    {
        $avgRating = $this->reviews()->avg('rating');
        $reviewsCount = $this->reviews()->count();
        $this->update([
            'average_rating' => round($avgRating, 2),
            'reviews_count' => $reviewsCount,
        ]);
    }

    public function incrementPrintCount(): void
    {
        $this->update([
            'print_count' => $this->print_count + 1,
            'last_printed_at' => now(),
        ]);
    }

    public function getProfitMarginAttribute(): float
    {
        if (!$this->cost_price || !$this->b2c_price) return 0;
        return round((($this->b2c_price - $this->cost_price) / $this->b2c_price) * 100, 2);
    }

    public function getProfitAmountAttribute(): float
    {
        if (!$this->cost_price) return 0;
        return round($this->b2c_price - $this->cost_price, 2);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeShowInB2C($query)
    {
        return $query->where('show_in_b2c', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeBestsellers($query)
    {
        return $query->where('is_bestseller', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_status', '!=', 'out_of_stock');
    }

    public function scopeB2C($query)
    {
        return $query->where('show_in_b2c', true);
    }

    public function scopeB2B($query)
    {
        return $query->where('show_in_b2b', true);
    }

    public function scopeOnSale($query)
    {
        return $query->where(function ($q) {
            $q->where('discount_amount', '>', 0)
              ->orWhere('discount_percentage', '>', 0);
        })->where(function ($q) {
            $q->whereNull('discount_starts_at')
              ->orWhere('discount_starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('discount_ends_at')
              ->orWhere('discount_ends_at', '>=', now());
        });
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name_ar', 'LIKE', "%{$term}%")
              ->orWhere('name_en', 'LIKE', "%{$term}%")
              ->orWhere('sku', 'LIKE', "%{$term}%")
              ->orWhere('barcode', 'LIKE', "%{$term}%")
              ->orWhere('description_ar', 'LIKE', "%{$term}%")
              ->orWhere('description_en', 'LIKE', "%{$term}%");
        });
    }

    public function scopeCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('b2c_price', [$min, $max]);
    }

    public function scopeSortBy($query, $sort, $direction = 'asc')
    {
        $sorts = [
            'price' => 'b2c_price',
            'name' => 'name_ar',
            'created' => 'created_at',
            'popular' => 'sales_count',
            'rating' => 'average_rating',
            'views' => 'views_count',
        ];
        $column = $sorts[$sort] ?? 'created_at';
        $direction = in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'asc';
        return $query->orderBy($column, $direction);
    }
}
