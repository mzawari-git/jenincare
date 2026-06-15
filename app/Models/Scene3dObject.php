<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scene3dObject extends Model
{
    protected $fillable = [
        'scene_id', 'model_path', 'object_type',
        'position_x', 'position_y', 'position_z',
        'rotation_x', 'rotation_y', 'rotation_z',
        'scale', 'color', 'is_walkable', 'is_collision',
        'label_ar', 'label_en', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'position_x' => 'float',
        'position_y' => 'float',
        'position_z' => 'float',
        'rotation_x' => 'float',
        'rotation_y' => 'float',
        'rotation_z' => 'float',
        'scale' => 'float',
        'is_walkable' => 'boolean',
        'is_collision' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scene(): BelongsTo
    {
        return $this->belongsTo(StoreScene::class, 'scene_id');
    }
}
