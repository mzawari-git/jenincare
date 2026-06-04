<?php

namespace App\Helpers;

class CtaHelper
{
    private static array $softCtas = [
        'learn_more' => 'اعرف المزيد',
        'explore' => 'استعرض المنتج',
        'discover' => 'اكتشف',
        'see_details' => 'عرض التفاصيل',
        'get_started' => 'ابدأ الآن',
        'show_more' => 'عرض المزيد',
        'find_out' => 'اكتشف المزيد',
    ];

    private static array $hardCtas = [
        'buy_now' => 'اشتري الآن',
        'add_to_cart' => 'أضف إلى السلة',
        'order_now' => 'اطلب الآن',
        'subscribe' => 'اشترك الآن',
        'get_offer' => 'احصل على العرض',
        'purchase' => 'شراء',
        'book_now' => 'احجز الآن',
    ];

    public static function getCta(string $key, array $context = []): string
    {
        $isBot = $context['is_bot'] ?? false;
        $isNewUser = $context['is_new_user'] ?? true;
        $score = $context['bot_score'] ?? 0;

        if ($isBot || $score > 70) {
            $pool = self::$softCtas;
        } elseif ($isNewUser) {
            $pool = self::$softCtas;
        } else {
            $pool = self::$hardCtas;
        }

        if (isset($pool[$key])) {
            return $pool[$key];
        }

        $pool = $isBot || $isNewUser ? self::$softCtas : self::$hardCtas;
        return reset($pool) ?: 'اعرف المزيد';
    }

    public static function getSoftCta(string $key): string
    {
        return self::$softCtas[$key] ?? 'اعرف المزيد';
    }

    public static function getHardCta(string $key): string
    {
        return self::$hardCtas[$key] ?? 'اشتري الآن';
    }

    public static function getAllSoft(): array
    {
        return self::$softCtas;
    }

    public static function getAllHard(): array
    {
        return self::$hardCtas;
    }

    public static function isSafePage(array $context): bool
    {
        return ($context['is_bot'] ?? false) || ($context['bot_score'] ?? 0) > 70;
    }
}
