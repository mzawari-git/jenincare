<?php

namespace App\Services\Sanitization;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class JunkFilter implements SanitizationStepInterface
{
    private array $blockedEmailPatterns = [
        '/@test\./i', '/@yopmail\./i', '/@mailinator\./i',
        '/@throwaway\./i', '/@tempmail\./i', '/@guerrillamail\./i',
        '/\.test$/i', '/example\./i',
    ];

    public function getName(): string
    {
        return 'Junk & Duplicate Filter';
    }

    public function process(array $payload, array $context = []): array
    {
        $email = $payload['data']['email'] ?? '';

        if (config('tracking.filtering.block_test_emails', true) && !empty($email)) {
            foreach ($this->blockedEmailPatterns as $pattern) {
                if (preg_match($pattern, $email)) {
                    Log::info('Event blocked by junk filter (test email)', [
                        'email' => $email,
                        'platform' => $context['platform'] ?? 'unknown',
                    ]);
                    return array_merge($payload, [
                        '_blocked' => true,
                        '_block_reason' => "Test email domain detected: {$email}",
                    ]);
                }
            }
        }

        $name = $payload['data']['product_name'] ?? $payload['data']['customer_name'] ?? '';
        $testPatterns = ['/^test$/i', '/^test order/i', '/^test product/i', '/^asdf/i', '/^qwerty/i'];
        foreach ($testPatterns as $pattern) {
            if (preg_match($pattern, $name)) {
                Log::info('Event blocked by junk filter (test name)', [
                    'name' => $name,
                    'platform' => $context['platform'] ?? 'unknown',
                ]);
                return array_merge($payload, [
                    '_blocked' => true,
                    '_block_reason' => "Test pattern detected in name: {$name}",
                ]);
            }
        }

        if (config('tracking.filtering.block_cod_high_cancellation', true)) {
            $customerPhone = $payload['data']['phone'] ?? $payload['data']['customer_phone'] ?? null;
            if ($customerPhone) {
                $threshold = config('tracking.filtering.cod_cancellation_threshold', 0.6);
                $codOrders = Order::where('customer_phone', $customerPhone)
                    ->where('payment_method', 'cod')
                    ->where('created_at', '>=', now()->subDays(90))
                    ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled', ['cancelled'])
                    ->first();

                if ($codOrders && $codOrders->total >= 3) {
                    $cancellationRate = $codOrders->cancelled / $codOrders->total;
                    if ($cancellationRate > $threshold) {
                        Log::info('Event blocked by junk filter (COD cancellation)', [
                            'phone' => $customerPhone,
                            'rate' => $cancellationRate,
                            'threshold' => $threshold,
                        ]);
                        return array_merge($payload, [
                            '_blocked' => true,
                            '_block_reason' => "COD cancellation rate {$cancellationRate} exceeds threshold {$threshold}",
                        ]);
                    }
                }
            }
        }

        return $payload;
    }
}
