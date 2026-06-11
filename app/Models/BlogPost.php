<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title_ar', 'slug', 'excerpt_ar', 'content_ar', 'category',
        'image', 'design_color', 'is_published', 'is_featured', 'sort_order',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            $defaults = [
                'articles' => 'uploads/blog/defaults/articles.svg',
                'tips' => 'uploads/blog/defaults/tips.svg',
                'news' => 'uploads/blog/defaults/news.svg',
                'guides' => 'uploads/blog/defaults/guides.svg',
            ];
            return asset($defaults[$this->category] ?? 'uploads/blog/defaults/articles.svg');
        }
        if (Str::startsWith($this->image, ['http://', 'https://'])) return $this->image;
        if (Str::startsWith($this->image, 'uploads/')) return asset($this->image);
        return asset('uploads/blog/' . basename($this->image));
    }

    public function getCategoryLabelAttribute(): string
    {
        $labels = [
            'articles' => 'مقالات عن المنتجات',
            'tips' => 'نصائح للعناية الشاملة',
            'news' => 'أخبار التجميل',
            'guides' => 'أدلة الاستخدام',
        ];
        return $labels[$this->category] ?? $this->category;
    }

    public function getCategoryColorAttribute(): string
    {
        $colors = [
            'articles' => '#ec4899',
            'tips' => '#0891b2',
            'news' => '#d4af37',
            'guides' => '#16a34a',
        ];
        return $colors[$this->category] ?? '#64748b';
    }
}
