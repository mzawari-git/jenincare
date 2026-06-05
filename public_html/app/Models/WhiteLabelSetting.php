<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteLabelSetting extends Model
{
    protected $fillable = [
        'clinic_id',
        'clinic_name',
        'logo_url',
        'primary_color',
        'accent_color',
        'theme_mode',
        'style_preset',
        'app_title',
        'fonts',
        'extra_css',
    ];

    protected $casts = [
        'fonts' => 'array',
        'extra_css' => 'array',
    ];

    public function clinic()
    {
        return $this->belongsTo(Company::class, 'clinic_id');
    }

    public function scopeForClinic($query, $clinicId = null)
    {
        if ($clinicId) {
            return $query->where('clinic_id', $clinicId);
        }
        return $query;
    }

    public static function getDefaults(): array
    {
        return [
            'clinic_name' => 'SkinAnalyzer',
            'logo_url' => '/android-chrome-512x512.png',
            'primary_color' => '#0D7CFF',
            'accent_color' => '#00BFA5',
            'theme_mode' => 'dark',
            'style_preset' => 'medicore',
            'app_title' => 'SkinAnalyzer',
        ];
    }
}
