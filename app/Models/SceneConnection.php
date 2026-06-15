<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SceneConnection extends Model
{
    protected $fillable = [
        'from_scene_id', 'to_scene_id',
        'direction', 'label_ar', 'label_en',
    ];

    public function fromScene(): BelongsTo
    {
        return $this->belongsTo(StoreScene::class, 'from_scene_id');
    }

    public function toScene(): BelongsTo
    {
        return $this->belongsTo(StoreScene::class, 'to_scene_id');
    }
}
