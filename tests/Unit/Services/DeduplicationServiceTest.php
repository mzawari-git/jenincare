<?php

namespace Tests\Unit\Services;

use App\Services\DeduplicationService;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class DeduplicationServiceTest extends TestCase
{
    private DeduplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->service = new DeduplicationService();
            $this->service->isDuplicate('_test_redis_connection_', ['platform' => '_test_']);
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis is not available: ' . $e->getMessage());
        }
    }

    public function test_is_not_duplicate_on_first_check()
    {
        $result = $this->service->isDuplicate('test_event_1', ['platform' => 'unit_test']);
        $this->assertFalse($result);
    }

    public function test_is_duplicate_on_second_check()
    {
        $this->service->isDuplicate('test_event_2', ['platform' => 'unit_test']);
        $result = $this->service->isDuplicate('test_event_2', ['platform' => 'unit_test']);
        $this->assertTrue($result);
    }

    public function test_different_event_ids_not_duplicate()
    {
        $this->service->isDuplicate('dup_event_a', ['platform' => 'unit_test']);
        $result = $this->service->isDuplicate('dup_event_b', ['platform' => 'unit_test']);
        $this->assertFalse($result);
    }

    public function test_different_platforms_not_duplicate()
    {
        $this->service->isDuplicate('cross_platform_event', ['platform' => 'facebook']);
        $result = $this->service->isDuplicate('cross_platform_event', ['platform' => 'tiktok']);
        $this->assertFalse($result);
    }
}
