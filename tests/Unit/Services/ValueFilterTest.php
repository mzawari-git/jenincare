<?php

namespace Tests\Unit\Services;

use App\Services\Sanitization\ValueFilter;
use Tests\TestCase;

class ValueFilterTest extends TestCase
{
    private ValueFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = app(ValueFilter::class);
    }

    public function test_get_name_returns_string()
    {
        $this->assertIsString($this->filter->getName());
    }

    public function test_process_returns_array()
    {
        $result = $this->filter->process(['data' => ['value' => 100]]);
        $this->assertIsArray($result);
    }

    public function test_process_allows_when_no_min_value_configured()
    {
        config()->set('tracking.filtering.min_order_value', 0);
        config()->set('tracking.filtering.min_margin_percent', 0);
        $result = $this->filter->process(['data' => ['value' => 1]]);
        $this->assertFalse($result['_blocked'] ?? false);
    }

    public function test_process_blocks_below_min_value()
    {
        config()->set('tracking.filtering.min_order_value', 50);
        config()->set('tracking.filtering.min_margin_percent', 0);
        $result = $this->filter->process(['data' => ['value' => 10]]);
        $this->assertTrue($result['_blocked'] ?? false);
    }

    public function test_process_allows_above_min_value()
    {
        config()->set('tracking.filtering.min_order_value', 50);
        config()->set('tracking.filtering.min_margin_percent', 0);
        $result = $this->filter->process(['data' => ['value' => 100]]);
        $this->assertFalse($result['_blocked'] ?? false);
    }

    public function test_process_with_platform_context()
    {
        config()->set('tracking.filtering.min_order_value', 10);
        $result = $this->filter->process(
            ['data' => ['value' => 1]],
            ['platform' => 'facebook']
        );
        $this->assertTrue($result['_blocked'] ?? false);
    }

    public function test_implements_interface()
    {
        $this->assertInstanceOf(\App\Services\Sanitization\SanitizationStepInterface::class, $this->filter);
    }
}
