<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreScene extends Model
{
    protected $fillable = [
        'name_ar', 'name_en', 'slug', 'section', 'aisle',
        'image_path', 'thumbnail', 'video_path', 'map_x', 'map_y',
        'sort_order', 'description_ar', 'description_en', 'is_active',
        '3d_enabled', 'ground_plane_url', 'skybox_url',
    ];

    protected $casts = [
        'map_x' => 'integer',
        'map_y' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        '3d_enabled' => 'boolean',
    ];

    public function hotspots(): HasMany
    {
        return $this->hasMany(SceneHotspot::class, 'scene_id');
    }

    public function connectionsFrom(): HasMany
    {
        return $this->hasMany(SceneConnection::class, 'from_scene_id');
    }

    public function connectionsTo(): HasMany
    {
        return $this->hasMany(SceneConnection::class, 'to_scene_id');
    }

    public function objects3d(): HasMany
    {
        return $this->hasMany(Scene3dObject::class, 'scene_id');
    }
}
