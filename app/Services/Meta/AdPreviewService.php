<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaAdCreative;

class AdPreviewService
{
    const PLACEMENTS = [
        'feed' => [
            'name' => 'فيسبوك Feed',
            'name_en' => 'Facebook Feed',
            'icon' => 'fab fa-facebook',
            'width' => 500,
            'height' => 600,
            'description' => 'يظهر في آخر الأخبار على سطح المكتب والجوال',
        ],
        'story' => [
            'name' => 'فيسبوك Stories',
            'name_en' => 'Facebook Stories',
            'icon' => 'fas fa-history',
            'width' => 360,
            'height' => 640,
            'description' => 'إعلان ملء الشاشة في القصص',
        ],
        'instagram_feed' => [
            'name' => 'Instagram Feed',
            'name_en' => 'Instagram Feed',
            'icon' => 'fab fa-instagram',
            'width' => 500,
            'height' => 600,
            'description' => 'يظهر في feed إنستغرام',
        ],
        'instagram_story' => [
            'name' => 'Instagram Stories',
            'name_en' => 'Instagram Stories',
            'icon' => 'fab fa-instagram',
            'width' => 360,
            'height' => 640,
            'description' => 'إعلان ملء الشاشة في قصص إنستغرام',
        ],
        'marketplace' => [
            'name' => 'Marketplace',
            'name_en' => 'Facebook Marketplace',
            'icon' => 'fas fa-store',
            'width' => 500,
            'height' => 400,
            'description' => 'يظهر في قسم Marketplace',
        ],
        'video_feeds' => [
            'name' => 'Video Feeds',
            'name_en' => 'In-Stream Video',
            'icon' => 'fas fa-video',
            'width' => 500,
            'height' => 400,
            'description' => 'إعلان داخل فيديوهات فيسبوك',
        ],
        'right_column' => [
            'name' => 'العمود الأيمن',
            'name_en' => 'Right Column',
            'icon' => 'fas fa-columns',
            'width' => 300,
            'height' => 250,
            'description' => 'يظهر في العمود الأيمن على سطح المكتب فقط',
        ],
        'search' => [
            'name' => 'نتائج البحث',
            'name_en' => 'Search Results',
            'icon' => 'fas fa-search',
            'width' => 500,
            'height' => 400,
            'description' => 'يظهر في نتائج بحث فيسبوك',
        ],
        'messenger_inbox' => [
            'name' => 'Messenger Inbox',
            'name_en' => 'Messenger Inbox',
            'icon' => 'fab fa-facebook-messenger',
            'width' => 400,
            'height' => 300,
            'description' => 'يظهر في صندوق وارد Messenger',
        ],
    ];

    public function getAllPlacements(): array
    {
        return self::PLACEMENTS;
    }

    public function getPlacement(string $key): ?array
    {
        return self::PLACEMENTS[$key] ?? null;
    }

    public function generatePreview(MetaAdCreative $creative, string $placement): array
    {
        $placementConfig = $this->getPlacement($placement);
        if (!$placementConfig) {
            return ['error' => 'الموقع غير معروف'];
        }

        return [
            'creative' => [
                'name' => $creative->name,
                'title' => $creative->title,
                'body' => $creative->body,
                'description' => $creative->description,
                'link_url' => $creative->link_url,
                'call_to_action' => $creative->call_to_action ?? 'LEARN_MORE',
                'image_hash' => $creative->image_hash,
                'image_url' => $creative->image_url,
            ],
            'placement' => $placementConfig,
            'preview_html' => $this->renderPreviewHtml($creative, $placementConfig, $placement),
            'specs' => [
                'recommended_image_size' => $this->getRecommendedImageSize($placement),
                'max_headline_length' => $this->getMaxHeadlineLength($placement),
                'max_body_length' => $this->getMaxBodyLength($placement),
                'max_description_length' => $this->getMaxDescriptionLength($placement),
            ],
        ];
    }

    public function getCreativePreview(MetaAdCreative $creative): array
    {
        $previews = [];
        foreach (self::PLACEMENTS as $key => $config) {
            $previews[$key] = $this->generatePreview($creative, $key);
        }
        return $previews;
    }

    public function validateAdContent(array $content): array
    {
        $issues = [];

        if (empty($content['title'])) {
            $issues[] = 'العنوان مطلوب';
        } elseif (mb_strlen($content['title']) > 40) {
            $issues[] = 'العنوان طويل جداً (الحد الأقصى 40 حرفاً)';
        }

        if (empty($content['body'])) {
            $issues[] = 'النص الأساسي مطلوب';
        } elseif (mb_strlen($content['body']) > 125) {
            $issues[] = 'النص الأساسي طويل جداً (الحد الأقصى 125 حرفاً للإصدار الرئيسي)';
        }

        if (!empty($content['description']) && mb_strlen($content['description']) > 30) {
            $issues[] = 'الوصف طويل جداً (الحد الأقصى 30 حرفاً)';
        }

        if (empty($content['link_url'])) {
            $issues[] = 'رابط الوجهة مطلوب';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'score' => empty($issues) ? 100 : max(0, 100 - (count($issues) * 20)),
        ];
    }

    public function getRecommendedImageSize(string $placement): array
    {
        return match ($placement) {
            'feed', 'search', 'video_feeds' => ['width' => '1200', 'height' => '628', 'ratio' => '1.91:1'],
            'story', 'instagram_story' => ['width' => '1080', 'height' => '1920', 'ratio' => '9:16'],
            'instagram_feed' => ['width' => '1080', 'height' => '1080', 'ratio' => '1:1'],
            'marketplace' => ['width' => '1200', 'height' => '628', 'ratio' => '1.91:1'],
            'right_column' => ['width' => '1200', 'height' => '628', 'ratio' => '1.91:1'],
            'messenger_inbox' => ['width' => '1200', 'height' => '628', 'ratio' => '1.91:1'],
            default => ['width' => '1200', 'height' => '628', 'ratio' => '1.91:1'],
        };
    }

    public function getMaxHeadlineLength(string $placement): int
    {
        return match ($placement) {
            'feed' => 40,
            'story' => 30,
            'instagram_feed' => 40,
            'instagram_story' => 30,
            'marketplace' => 40,
            'right_column' => 25,
            default => 40,
        };
    }

    public function getMaxBodyLength(string $placement): int
    {
        return match ($placement) {
            'feed' => 125,
            'story' => 80,
            'instagram_feed' => 125,
            'instagram_story' => 80,
            default => 125,
        };
    }

    public function getMaxDescriptionLength(string $placement): int
    {
        return match ($placement) {
            'feed' => 30,
            'right_column' => 20,
            default => 30,
        };
    }

    private function renderPreviewHtml(MetaAdCreative $creative, array $placement, string $placementKey): string
    {
        $title = e($creative->title ?? '');
        $body = e($creative->body ?? '');
        $description = e($creative->description ?? '');
        $cta = $this->getCtaLabel($creative->call_to_action ?? 'LEARN_MORE');
        $linkUrl = e($creative->link_url ?? '#');

        $imageHtml = $creative->image_hash
            ? '<div class="ad-preview-img" style="background:#e9ecef;height:180px;display:flex;align-items:center;justify-content:center;color:#adb5bd;"><i class="fas fa-image fa-4x"></i></div>'
            : '<div class="ad-preview-img" style="background:linear-gradient(135deg,#667eea,#764ba2);height:180px;display:flex;align-items:center;justify-content:center;color:#fff;"><i class="fas fa-ad fa-4x"></i></div>';

        $isStory = in_array($placementKey, ['story', 'instagram_story']);

        if ($isStory) {
            return <<<HTML
            <div style="width:{$placement['width']}px;height:{$placement['height']}px;background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:20px;overflow:hidden;position:relative;font-family:sans-serif;">
                <div style="position:absolute;top:40px;left:0;right:0;text-align:center;padding:20px;">
                    <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#f093fb,#f5576c);margin:0 auto 15px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px;"><i class="fas fa-ad"></i></div>
                    <div style="color:#fff;font-size:16px;font-weight:bold;margin-bottom:10px;">{$title}</div>
                    <div style="color:rgba(255,255,255,0.8);font-size:13px;margin-bottom:20px;padding:0 20px;">{$body}</div>
                    <div style="background:rgba(255,255,255,0.2);border-radius:20px;padding:10px 20px;display:inline-block;color:#fff;font-size:13px;">{$cta}</div>
                </div>
                <div style="position:absolute;bottom:20px;left:0;right:0;text-align:center;color:rgba(255,255,255,0.4);font-size:10px;">إعلان مدفوع • {$placement['name']}</div>
            </div>
HTML;
        }

        return <<<HTML
        <div style="width:{$placement['width']}px;border:1px solid #e0e0e0;border-radius:8px;overflow:hidden;font-family:sans-serif;background:#fff;direction:rtl;">
            <div style="padding:12px 16px;display:flex;align-items:center;border-bottom:1px solid #f0f0f0;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1877F2,#0c5dbf);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;margin-left:10px;"><i class="fab fa-facebook"></i></div>
                <div style="flex:1;"><div style="font-weight:600;font-size:13px;">إعلان مدعوم</div><div style="color:#65676b;font-size:11px;">مدعوم • <i class="fas fa-globe"></i></div></div>
                <div style="color:#65676b;font-size:16px;cursor:pointer;"><i class="fas fa-times"></i></div>
            </div>
            {$imageHtml}
            <div style="padding:10px 16px;">
                <div style="color:#65676b;font-size:12px;margin-bottom:4px;">{$linkUrl}</div>
                <div style="font-weight:600;font-size:15px;margin-bottom:4px;color:#1c1e21;">{$title}</div>
                <div style="color:#65676b;font-size:13px;margin-bottom:6px;line-height:1.4;">{$body}</div>
                <div style="color:#65676b;font-size:12px;border-top:1px solid #dadde1;padding-top:6px;">{$description}</div>
            </div>
            <div style="border-top:1px solid #dadde1;padding:8px 16px;text-align:center;">
                <span style="color:#1877F2;font-weight:600;font-size:13px;cursor:pointer;">{$cta}</span>
            </div>
            <div style="background:#f0f2f5;padding:8px 16px;display:flex;align-items:center;gap:12px;font-size:12px;color:#65676b;">
                <span><i class="far fa-thumbs-up"></i> إعجاب</span>
                <span><i class="far fa-comment"></i> تعليق</span>
                <span><i class="fas fa-share"></i> مشاركة</span>
            </div>
        </div>
HTML;
    }

    private function getCtaLabel(string $cta): string
    {
        return match ($cta) {
            'LEARN_MORE' => 'اعرف المزيد',
            'SHOP_NOW' => 'تسوق الآن',
            'SIGN_UP' => 'اشترك',
            'BOOK_NOW' => 'احجز الآن',
            'CONTACT_US' => 'اتصل بنا',
            'GET_OFFER' => 'احصل على العرض',
            'GET_QUOTE' => 'احصل على عرض سعر',
            'DOWNLOAD' => 'تحميل',
            'INSTALL' => 'تثبيت',
            'SUBSCRIBE' => 'اشترك',
            default => 'اعرف المزيد',
        };
    }
}
