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
        'category',
        'prompt_text',
        'tone',
        'is_active',
        'language',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForLanguage($query, string $language)
    {
        return $query->whereIn('language', [$language, 'both']);
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
        $text = $this->prompt_text;

        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }

        return $text;
    }
}
