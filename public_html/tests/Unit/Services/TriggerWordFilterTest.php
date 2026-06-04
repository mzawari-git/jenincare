<?php

namespace Tests\Unit\Services;

use App\Services\Sanitization\TriggerWordFilter;
use App\Models\TriggerWord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TriggerWordFilterTest extends TestCase
{
    use RefreshDatabase;

    private TriggerWordFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = app(TriggerWordFilter::class);
    }

    public function test_get_name_returns_string()
    {
        $this->assertIsString($this->filter->getName());
    }

    public function test_process_returns_array()
    {
        $result = $this->filter->process(['data' => ['product_name' => 'Normal product']]);
        $this->assertIsArray($result);
    }

    public function test_clean_text_passes_through()
    {
        $result = $this->filter->process(
            ['data' => ['product_name' => 'A clean product description']],
            ['platform' => 'facebook']
        );
        $this->assertFalse($result['_blocked'] ?? false);
    }

    public function test_trigger_word_block_action()
    {
        TriggerWord::create([
            'word' => 'testtriggerword123',
            'category' => 'medical',
            'severity' => 'high',
            'platform' => 'facebook',
            'action' => 'block',
            'is_active' => true,
        ]);

        $result = $this->filter->process(
            ['data' => ['product_name' => 'This has testtriggerword123 in it']],
            ['platform' => 'facebook']
        );
        $this->assertTrue($result['_blocked'] ?? false);
    }

    public function test_trigger_word_remove_action()
    {
        TriggerWord::create([
            'word' => 'remove_me_word',
            'category' => 'medical',
            'severity' => 'high',
            'platform' => 'facebook',
            'action' => 'remove',
            'is_active' => true,
        ]);

        $result = $this->filter->process(
            ['data' => ['product_name' => 'Text with remove_me_word inside']],
            ['platform' => 'facebook']
        );
        $this->assertStringNotContainsStringIgnoringCase('remove_me_word', $result['data']['product_name'] ?? '');
    }

    public function test_platform_specific_trigger_word()
    {
        TriggerWord::create([
            'word' => 'meta_only_word',
            'category' => 'financial',
            'severity' => 'medium',
            'platform' => 'facebook',
            'action' => 'block',
            'is_active' => true,
        ]);

        $resultFb = $this->filter->process(
            ['data' => ['product_name' => 'meta_only_word test']],
            ['platform' => 'facebook']
        );
        $resultTt = $this->filter->process(
            ['data' => ['product_name' => 'meta_only_word test']],
            ['platform' => 'tiktok']
        );

        $this->assertTrue($resultFb['_blocked'] ?? false);
        $this->assertFalse($resultTt['_blocked'] ?? false);
    }

    public function test_empty_payload()
    {
        $result = $this->filter->process(['data' => []]);
        $this->assertIsArray($result);
    }

    public function test_implements_interface()
    {
        $this->assertInstanceOf(\App\Services\Sanitization\SanitizationStepInterface::class, $this->filter);
    }
}
