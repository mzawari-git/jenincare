<?php

namespace Tests\Feature\Tracking;

use App\Models\TriggerWord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriggerWordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TriggerWord::create([
            'word' => 'miracle cure',
            'action' => 'block',
            'platform' => 'facebook',
            'severity' => 'critical',
            'is_active' => true,
        ]);

        TriggerWord::create([
            'word' => 'lose weight fast',
            'action' => 'block',
            'platform' => 'facebook',
            'severity' => 'critical',
            'is_active' => true,
        ]);

        TriggerWord::create([
            'word' => 'click here',
            'action' => 'replace',
            'replacement' => 'visit',
            'platform' => 'facebook',
            'severity' => 'medium',
            'is_active' => true,
        ]);

        TriggerWord::create([
            'word' => 'exclusive deal',
            'action' => 'remove',
            'platform' => 'facebook',
            'severity' => 'medium',
            'is_active' => true,
        ]);

        TriggerWord::create([
            'word' => 'yopmail.com',
            'action' => 'block',
            'platform' => null,
            'severity' => 'critical',
            'is_active' => true,
        ]);
    }

    public function test_trigger_word_filter_blocks_critical_words()
    {
        $pipeline = app(\App\Services\Sanitization\SanitizationPipeline::class);
        $result = $pipeline->process(
            ['data' => ['description' => 'This miracle cure will make you lose weight fast!']],
            ['platform' => 'facebook']
        );
        $this->assertTrue($result['_blocked']);
    }

    public function test_trigger_word_filter_replaces_medium_words()
    {
        $pipeline = app(\App\Services\Sanitization\SanitizationPipeline::class);
        $result = $pipeline->process(
            ['data' => ['description' => 'Click here to learn more about our exclusive deal']],
            ['platform' => 'facebook']
        );
        $this->assertStringContainsString('learn more', $result['data']['description']);
    }

    public function test_clean_text_passes_through()
    {
        $pipeline = app(\App\Services\Sanitization\SanitizationPipeline::class);
        $result = $pipeline->process(
            ['data' => ['description' => 'Natural skin care products for daily use']],
            ['platform' => 'facebook']
        );
        $this->assertFalse($result['_blocked']);
    }

    public function test_test_email_is_blocked()
    {
        $pipeline = app(\App\Services\Sanitization\SanitizationPipeline::class);
        $result = $pipeline->process(
            ['data' => ['email' => 'test@yopmail.com', 'description' => 'Product']],
            ['platform' => 'facebook']
        );
        $this->assertTrue($result['_blocked']);
    }
}
