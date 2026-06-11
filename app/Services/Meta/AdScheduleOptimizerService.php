<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaCampaign;

class AdScheduleOptimizerService
{
    public function getBestTimes(): array
    {
        return [
            'days' => [
                'saturday' => ['name' => 'السبت', 'best_hours' => ['10:00', '14:00', '20:00'], 'score' => 85],
                'sunday' => ['name' => 'الأحد', 'best_hours' => ['09:00', '12:00', '18:00'], 'score' => 75],
                'monday' => ['name' => 'الإثنين', 'best_hours' => ['08:00', '12:00', '19:00'], 'score' => 70],
                'tuesday' => ['name' => 'الثلاثاء', 'best_hours' => ['09:00', '13:00', '20:00'], 'score' => 72],
                'wednesday' => ['name' => 'الأربعاء', 'best_hours' => ['10:00', '14:00', '21:00'], 'score' => 68],
                'thursday' => ['name' => 'الخميس', 'best_hours' => ['11:00', '15:00', '22:00'], 'score' => 78],
                'friday' => ['name' => 'الجمعة', 'best_hours' => ['09:00', '13:00', '17:00', '22:00'], 'score' => 90],
            ],
            'general_recommendations' => [
                'أفضل أيام الأسبوع: الجمعة والسبت',
                'أوقات الذروة: 10 صباحاً - 12 ظهراً، 8 مساءً - 11 مساءً',
                'تجنب أوقات العمل الرسمية (8 صباحاً - 4 عصراً) للإعلانات الترفيهية',
                'منتصف الأسبوع (الإثنين-الأربعاء) مناسب للإعلانات الاحترافية',
            ],
        ];
    }

    public function getCampaignScheduleRecommendations(int $campaignId): array
    {
        $campaign = MetaCampaign::find($campaignId);
        if (!$campaign) {
            return ['message' => 'الحملة غير موجودة'];
        }

        $insights = $campaign->insights[0] ?? [];
        $spend = $insights['spend'] ?? 0;
        $impressions = $insights['impressions'] ?? 0;
        $clicks = $insights['clicks'] ?? 0;

        $bestTimes = $this->getBestTimes();
        $industry = $this->detectIndustry($campaign->name);

        return [
            'campaign' => $campaign->name,
            'objective' => $campaign->objective,
            'industry' => $industry,
            'best_times' => $bestTimes['days'],
            'general_recommendations' => $bestTimes['general_recommendations'],
            'specific_recommendations' => $this->getSpecificRecommendations($campaign, $industry),
            'schedule_suggestion' => $this->generateScheduleSuggestion($industry, $campaign->objective),
        ];
    }

    public function generateWeeklySchedule(array $preferences = []): array
    {
        $bestTimes = $this->getBestTimes();
        $schedule = [];

        foreach ($bestTimes['days'] as $day => $info) {
            $schedule[$day] = [
                'name' => $info['name'],
                'score' => $info['score'],
                'active_hours' => $info['best_hours'],
                'suggested_budget_distribution' => round(($info['score'] / 100) * 20, 1) . '%',
            ];
        }

        return [
            'schedule' => $schedule,
            'total_budget_distribution' => 'وزع الميزانية حسب score اليوم',
            'note' => 'يمكن تعديل الجدول بناءً على أداء الحملات السابقة',
        ];
    }

    private function detectIndustry(string $campaignName): string
    {
        $campaignName = mb_strtolower($campaignName);

        $keywords = [
            'beauty' => ['تجميل', 'عناية', 'بشرة', 'شعر', 'مكياج', 'جل', 'مساج', 'سبا', 'nail', 'makeup'],
            'salon' => ['صالون', 'كوافير', 'حلاق', 'تصفيف', 'تسريحة'],
            'medical' => ['طبي', 'عيادة', 'دكتور', 'صحة', 'علاج', 'طبيب', 'مستشفى'],
            'fashion' => ['أزياء', 'موضة', 'ملابس', 'فساتين', 'إكسسوارات'],
            'food' => ['مطعم', 'طعام', 'أكل', 'وجبة', 'قهوة', 'كافيه'],
        ];

        foreach ($keywords as $industry => $words) {
            foreach ($words as $word) {
                if (mb_strpos($campaignName, $word) !== false) {
                    return $industry;
                }
            }
        }

        return 'general';
    }

    private function getSpecificRecommendations(MetaCampaign $campaign, string $industry): array
    {
        $recommendations = [
            'حاول جدولة الإعلانات في أوقات الذروة لزيادة التفاعل',
        ];

        if ($industry === 'beauty' || $industry === 'salon') {
            $recommendations[] = 'ركز على الفترة المسائية (8-11 مساءً) حيث تتصفح العميلات';
            $recommendations[] = 'يوم الجمعة والسبت هما الأفضل للحجز المسبق';
            $recommendations[] = 'تجنب الصباح الباكر (قبل 9 صباحاً)';
        }

        if ($campaign->objective === 'OUTCOME_LEADS') {
            $recommendations[] = 'أوقات الظهيرة والمساء المبكر مناسبة لجذب العملاء المحتملين';
        }

        return $recommendations;
    }

    private function generateScheduleSuggestion(string $industry, string $objective): array
    {
        return [
            'monday_thursday' => [
                'active' => false,
                'hours' => '10:00 - 14:00 و 20:00 - 23:00',
                'budget' => '40% من الميزانية',
            ],
            'friday_saturday' => [
                'active' => true,
                'hours' => '09:00 - 23:00 (طوال اليوم)',
                'budget' => '40% من الميزانية',
            ],
            'sunday' => [
                'active' => false,
                'hours' => '12:00 - 16:00 و 19:00 - 22:00',
                'budget' => '20% من الميزانية',
            ],
        ];
    }
}
