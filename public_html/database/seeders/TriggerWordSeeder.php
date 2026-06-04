<?php

namespace Database\Seeders;

use App\Models\TriggerWord;
use Illuminate\Database\Seeder;

class TriggerWordSeeder extends Seeder
{
    private array $words = [
        // Medical Claims
        ['cure', 'medical_claims', 'high', 'replace', '**[supports]**'],
        ['treat', 'medical_claims', 'high', 'replace', '**[helps with]**'],
        ['heal', 'medical_claims', 'critical', 'replace', '**[supports recovery]**'],
        ['miracle', 'medical_claims', 'critical', 'block', null],
        ['guaranteed results', 'medical_claims', 'critical', 'block', null],
        ['no side effects', 'medical_claims', 'high', 'block', null],
        ['medically proven', 'medical_claims', 'high', 'replace', '**[widely used]**'],
        ['clinical trial', 'medical_claims', 'high', 'replace', '**[popular choice]**'],
        ['instant relief', 'medical_claims', 'high', 'replace', '**[effective relief]**'],
        ['permanent solution', 'medical_claims', 'high', 'block', null],

        // Weight Loss
        ['lose weight fast', 'weight_loss', 'critical', 'block', null],
        ['weight loss guarantee', 'weight_loss', 'critical', 'block', null],
        ['slim down quickly', 'weight_loss', 'high', 'replace', '**[fit comfortably]**'],
        ['fat burner', 'weight_loss', 'high', 'block', null],
        ['skinny', 'weight_loss', 'medium', 'replace', '**[fit]**'],
        ['diet pill', 'weight_loss', 'critical', 'block', null],

        // Financial Claims
        ['get rich', 'financial', 'critical', 'block', null],
        ['make money fast', 'financial', 'critical', 'block', null],
        ['passive income guarantee', 'financial', 'high', 'block', null],
        ['financial freedom', 'financial', 'medium', 'replace', '**[ financial wellness]**'],
        ['guaranteed income', 'financial', 'high', 'block', null],
        ['risk-free investment', 'financial', 'critical', 'block', null],

        // Before/After
        ['before and after', 'before_after', 'high', 'replace', '**[transformation]**'],
        ['results may vary', 'before_after', 'medium', 'remove', null],
        ['see results in', 'before_after', 'high', 'replace', '**[experience benefits in]**'],

        // Unrealistic Beauty
        ['anti-aging', 'beauty_claims', 'high', 'replace', '**[age-defying]**'],
        ['look younger', 'beauty_claims', 'high', 'replace', '**[feel refreshed]**'],
        ['reverse aging', 'beauty_claims', 'critical', 'block', null],
        ['wrinkle-free', 'beauty_claims', 'medium', 'replace', '**[smooth-looking]**'],
        ['perfect skin', 'beauty_claims', 'medium', 'replace', '**[healthy-looking skin]**'],

        // Profanity & Discrimination
        ['hate', 'profanity', 'critical', 'block', null],
        ['stupid', 'profanity', 'high', 'block', null],
        ['idiot', 'profanity', 'high', 'block', null],
        ['discrimination', 'discrimination', 'critical', 'block', null],
        ['racist', 'discrimination', 'critical', 'block', null],
        ['sexist', 'discrimination', 'critical', 'block', null],

        // Test Email Domains
        ['@test.com', 'test_email', 'medium', 'block', null],
        ['@yopmail.com', 'test_email', 'medium', 'block', null],
        ['@mailinator.com', 'test_email', 'medium', 'block', null],
        ['@throwaway.com', 'test_email', 'medium', 'block', null],
        ['@tempmail.com', 'test_email', 'medium', 'block', null],
        ['@guerrillamail.com', 'test_email', 'medium', 'block', null],

        // Misleading
        ['click here', 'misleading', 'medium', 'replace', '**[learn more]**'],
        ['act now', 'misleading', 'medium', 'replace', '**[discover more]**'],
        ['limited time offer', 'misleading', 'low', 'replace', '**[while supplies last]**'],
        ['exclusive deal', 'misleading', 'low', 'replace', '**[special offer]**'],
        ['don\'t miss out', 'misleading', 'medium', 'replace', '**[check it out]**'],

        // Scam Indicators
        ['free money', 'scam', 'critical', 'block', null],
        ['earn extra cash', 'scam', 'high', 'block', null],
        ['work from home', 'scam', 'high', 'block', null],
        ['sign up bonus', 'scam', 'medium', 'replace', '**[welcome offer]**'],
        ['no experience needed', 'scam', 'high', 'replace', '**[easy to start]**'],

        // Meta-Specific
        ['covid', 'meta_policy', 'critical', 'block', null],
        ['coronavirus', 'meta_policy', 'critical', 'block', null],
        ['pandemic', 'meta_policy', 'high', 'block', null],
        ['vaccine', 'meta_policy', 'high', 'block', null],
        ['lockdown', 'meta_policy', 'high', 'block', null],
        ['social distancing', 'meta_policy', 'medium', 'remove', null],

        // Laser / Medical Device Filtering — لتجاوز فلاتر المنصات الإعلانية
        ['ليزر', 'laser_devices', 'critical', 'replace', 'نبض ضوئي'],
        ['laser', 'laser_devices', 'critical', 'replace', 'IPL'],
        ['lazer', 'laser_devices', 'critical', 'replace', 'IPL'],
        ['إزالة الشعر بالليزر', 'laser_devices', 'critical', 'replace', 'إزالة الشعر بالنبض الضوئي'],
        ['laser hair removal', 'laser_devices', 'critical', 'replace', 'IPL hair removal'],
        ['fractional', 'laser_devices', 'high', 'replace', 'micro-needling'],
        ['co2 laser', 'laser_devices', 'critical', 'replace', 'advanced skin device'],
        ['q-switched', 'laser_devices', 'high', 'replace', 'pigment device'],
        ['nd:yag', 'laser_devices', 'high', 'replace', 'skin rejuvenation device'],
        ['alma soprano', 'laser_devices', 'high', 'replace', 'advanced hair removal device'],

        // Engagement Bait — عبارات حث إلزامية
        ['شارك الآن', 'engagement_bait', 'high', 'replace', 'يمكنك المشاركة'],
        ['اعمل تاج', 'engagement_bait', 'high', 'replace', 'شارك مع من يهمك'],
        ['تعليق + لايك', 'engagement_bait', 'critical', 'replace', 'نرحب بتعليقاتك'],
        ['comment now', 'engagement_bait', 'high', 'replace', 'feel free to comment'],
        ['tag your friends', 'engagement_bait', 'high', 'replace', 'share with friends'],
        ['like and share', 'engagement_bait', 'high', 'replace', 'discover more'],
        ['share to win', 'engagement_bait', 'critical', 'replace', 'check our offers'],
        ['comment to win', 'engagement_bait', 'critical', 'replace', 'explore our collection'],
    ];

    public function run(): void
    {
        $platforms = ['facebook', 'tiktok', 'google', null];

        foreach ($this->words as $word) {
            foreach ($platforms as $platform) {
                TriggerWord::create([
                    'word' => $word[0],
                    'category' => $word[1],
                    'severity' => $word[2],
                    'action' => $word[3],
                    'replacement' => $word[4] ?? null,
                    'platform' => $platform,
                    'active' => true,
                ]);
            }
        }

        $this->command->info('Seeded ' . count($this->words) . ' trigger words across ' . count($platforms) . ' platforms.');
    }
}
