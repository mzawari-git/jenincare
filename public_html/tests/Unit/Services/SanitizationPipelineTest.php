<?php

namespace Tests\Unit\Services;

use App\Services\Sanitization\SanitizationPipeline;
use App\Services\Sanitization\TriggerWordFilter;
use App\Services\Sanitization\JunkFilter;
use App\Services\Sanitization\ValueFilter;
use Tests\TestCase;

class SanitizationPipelineTest extends TestCase
{
    private SanitizationPipeline $pipeline;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pipeline = new SanitizationPipeline();
    }

    public function test_process_returns_array()
    {
        $result = $this->pipeline->process(['data' => ['value' => 100]]);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('_sanitization_log', $result);
        $this->assertArrayHasKey('_sanitized', $result);
        $this->assertArrayHasKey('_blocked', $result);
    }

    public function test_process_returns_input_when_no_steps()
    {
        $result = $this->pipeline->process(['data' => ['value' => 100]]);
        $this->assertFalse($result['_blocked']);
        $this->assertEquals(['data' => ['value' => 100]], array_diff_key($result, array_flip(['_sanitization_log', '_sanitized', '_blocked'])));
    }

    public function test_add_step_returns_self()
    {
        $result = $this->pipeline->addStep(app(JunkFilter::class));
        $this->assertSame($this->pipeline, $result);
    }

    public function test_get_steps_returns_array()
    {
        $this->pipeline->addStep(app(JunkFilter::class));
        $this->pipeline->addStep(app(ValueFilter::class));
        $steps = $this->pipeline->getSteps();
        $this->assertIsArray($steps);
        $this->assertCount(2, $steps);
    }

    public function test_pipeline_blocks_with_junk_filter()
    {
        $this->pipeline->addStep(app(JunkFilter::class));
        $result = $this->pipeline->process(['data' => ['email' => 'test@test.com']]);
        $this->assertTrue($result['_blocked']);
    }

    public function test_pipeline_allows_clean_data()
    {
        $this->pipeline->addStep(app(JunkFilter::class));
        $result = $this->pipeline->process(['data' => ['email' => 'legit@company.com']]);
        $this->assertFalse($result['_blocked']);
    }

    public function test_process_with_platform_context()
    {
        $this->pipeline->addStep(app(JunkFilter::class));
        $result = $this->pipeline->process(
            ['data' => ['email' => 'user@mailinator.com']],
            ['platform' => 'facebook']
        );
        $this->assertTrue($result['_blocked']);
        $this->assertStringContainsString('mailinator', $result['_block_reason']);
    }
}
