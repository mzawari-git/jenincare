<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SceneHotspot extends Model
{
    protected $fillable = [
        'scene_id', 'product_id', 'pitch', 'yaw',
        'label_ar', 'label_en', 'icon_type', 'is_active',
    ];

    protected $casts = [
        'pitch' => 'float',
        'yaw' => 'float',
        'is_active' => 'boolean',
    ];

    public function scene(): BelongsTo
    {
        return $this->belongsTo(StoreScene::class, 'scene_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
