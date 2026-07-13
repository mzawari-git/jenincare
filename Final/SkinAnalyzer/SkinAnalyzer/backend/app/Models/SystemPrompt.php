<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemPrompt extends Model
{
    use HasFactory;

    protected $table = 'system_prompts';

    protected $fillable = [
        'name',
        'provider_key',
        'system_instruction',
        'tone',
        'is_active',
        'version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForProvider($query, string $providerKey)
    {
        return $query->where('provider_key', $providerKey);
    }

    public function getAvailableVariables(): array
    {
        return [
            '{skin_type}',
            '{skin_concerns}',
            '{defects}',
            '{product_names}',
            '{product_recommendations}',
            '{health_score}',
            '{radar_metrics}',
            '{user_name}',
            '{app_name}',
            '{date}',
        ];
    }

    public function render(array $variables): string
    {
        $text = $this->system_instruction;

        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }

        return $text;
    }
}
