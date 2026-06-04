<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HeroSlide extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id', 'title_ar', 'title_en', 'subtitle_ar', 'subtitle_en',
        'description_ar', 'description_en', 'button_text_ar', 'button_text_en',
        'button_url', 'second_button_text_ar', 'second_button_url',
        'image', 'mobile_image', 'video_url', 'html_content',
        'image_position', 'text_color', 'text_align', 'overlay_opacity',
        'animation_type', 'parallax', 'full_width_image', 'content_width',
        'badge_text_ar', 'badge_text_en',
        'gradient_from', 'gradient_to', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'parallax' => 'boolean',
        'full_width_image' => 'boolean',
        'overlay_opacity' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getTitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->title_ar : ($this->title_en ?: $this->title_ar);
    }

    public function getSubtitleAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->subtitle_ar : ($this->subtitle_en ?: $this->subtitle_ar);
    }

    public function getDescriptionAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : ($this->description_en ?: $this->description_ar);
    }

    public function getButtonTextAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->button_text_ar : ($this->button_text_en ?: $this->button_text_ar);
    }

    public function getBadgeTextAttribute()
    {
        return app()->getLocale() === 'ar' ? $this->badge_text_ar : ($this->badge_text_en ?: $this->badge_text_ar);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'http')) {
                return $this->image;
            }
            return url('files/' . $this->image);
        }
        if ($this->product && $this->product->main_image) {
            return url('files/' . $this->product->main_image);
        }
        return null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
