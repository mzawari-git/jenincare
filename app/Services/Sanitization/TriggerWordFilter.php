<?php

namespace App\Services\Sanitization;

use App\Models\TriggerWord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TriggerWordFilter implements SanitizationStepInterface
{
    public function getName(): string
    {
        return 'Trigger Word Filter';
    }

    public function process(array $payload, array $context = []): array
    {
        $platform = $context['platform'] ?? null;
        $triggerWords = TriggerWord::active()->forPlatform($platform)->get();

        if ($triggerWords->isEmpty()) {
            return $payload;
        }

        $fieldsToCheck = ['product_name', 'description', 'product_category', 'search_string', 'content_name'];
        $found = [];

        foreach ($fieldsToCheck as $field) {
            if (empty($payload['data'][$field])) continue;

            $original = $payload['data'][$field];
            $modified = $original;

            foreach ($triggerWords as $tw) {
                $pattern = '/' . preg_quote($tw->word, '/') . '/iu';

                switch ($tw->action) {
                    case 'block':
                        if (preg_match($pattern, $modified)) {
                            Log::warning('Event blocked by trigger word', [
                                'word' => $tw->word,
                                'field' => $field,
                                'platform' => $platform,
                            ]);
                            return array_merge($payload, ['_blocked' => true, '_block_reason' => "Trigger word '{$tw->word}' in {$field}"]);
                        }
                        break;

                    case 'remove':
                        $modified = preg_replace($pattern, '', $modified);
                        $found[] = ['word' => $tw->word, 'action' => 'remove', 'field' => $field];
                        break;

                    case 'replace':
                        $replacement = $tw->replacement ?? '***';
                        $modified = preg_replace($pattern, $replacement, $modified);
                        $found[] = ['word' => $tw->word, 'action' => 'replace', 'field' => $field];
                        break;
                }
            }

            $payload['data'][$field] = trim($modified);
        }

        if (!empty($found)) {
            $payload['_sanitized'] = true;
            $payload['_sanitization_log'][] = [
                'step' => $this->getName(),
                'changes' => $found,
            ];
        }

        return $payload;
    }
}
