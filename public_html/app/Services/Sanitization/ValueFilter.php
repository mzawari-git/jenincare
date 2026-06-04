<?php

namespace App\Services\Sanitization;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class ValueFilter implements SanitizationStepInterface
{
    public function getName(): string
    {
        return 'Value & Margin Filter';
    }

    public function process(array $payload, array $context = []): array
    {
        $minValue = (float) config('tracking.filtering.min_order_value', 0);
        $minMargin = (float) config('tracking.filtering.min_margin_percent', 0);

        if ($minValue > 0 || $minMargin > 0) {
            $value = (float) ($payload['data']['value'] ?? $payload['data']['price'] ?? 0);
            $currency = $payload['data']['currency'] ?? 'ILS';

            if ($minValue > 0 && $value < $minValue) {
                Log::info('Event blocked by value filter', [
                    'value' => $value,
                    'min_value' => $minValue,
                    'currency' => $currency,
                    'platform' => $context['platform'] ?? 'unknown',
                ]);
                return array_merge($payload, [
                    '_blocked' => true,
                    '_block_reason' => "Order value {$value} below minimum {$minValue}",
                ]);
            }

            if ($minMargin > 0 && !empty($payload['data']['contents'])) {
                $items = $payload['data']['contents'];
                $totalCost = 0;
                $totalRevenue = 0;

                foreach ($items as $item) {
                    $price = (float) ($item['price'] ?? 0);
                    $cost = (float) ($item['cost'] ?? $item['price'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 1);
                    $totalRevenue += $price * $qty;
                    $totalCost += $cost * $qty;
                }

                if ($totalRevenue > 0) {
                    $margin = (($totalRevenue - $totalCost) / $totalRevenue) * 100;
                    if ($margin < $minMargin) {
                        Log::info('Event blocked by margin filter', [
                            'margin' => $margin,
                            'min_margin' => $minMargin,
                            'platform' => $context['platform'] ?? 'unknown',
                        ]);
                        return array_merge($payload, [
                            '_blocked' => true,
                            '_block_reason' => "Order margin {$margin}% below minimum {$minMargin}%",
                        ]);
                    }
                }
            }
        }

        return $payload;
    }
}
