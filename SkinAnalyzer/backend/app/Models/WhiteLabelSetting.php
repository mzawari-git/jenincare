<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhiteLabelSetting extends Model
{
    use HasFactory;

    protected $table = 'white_label_settings';

    protected $fillable = [
        'app_name_ar',
        'app_name_en',
        'primary_color',
        'accent_color',
        'background_color',
        'logo_path',
        'favicon_path',
        'server_url',
        'privacy_policy_url',
        'app_store_url',
        'google_play_url',
        'social_facebook',
        'social_instagram',
        'social_twitter',
        'welcome_message_ar',
        'welcome_message_en',
        'is_customized',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->logo_path)) {
            return null;
        }

        return url('storage/' . $this->logo_path);
    }

    public function getFaviconUrlAttribute(): ?string
    {
        if (empty($this->favicon_path)) {
            return null;
        }

        return url('storage/' . $this->favicon_path);
    }

    public static function getCurrent(): ?self
    {
        return static::first();
    }
}
