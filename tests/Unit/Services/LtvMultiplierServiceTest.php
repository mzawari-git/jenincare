<?php

namespace Tests\Unit\Services;

use App\Services\LtvMultiplierService;
use Tests\TestCase;

class LtvMultiplierServiceTest extends TestCase
{
    private LtvMultiplierService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(LtvMultiplierService::class);
    }

    public function test_get_multiplier_returns_float()
    {
        $multiplier = $this->service->getMultiplier('b2b', 'facebook');
        $this->assertIsFloat($multiplier);
        $this->assertGreaterThan(0, $multiplier);
    }

    public function test_get_multiplier_for_b2b_higher_than_b2c()
    {
        $b2bMult = $this->service->getMultiplier('b2b', 'facebook');
        $b2cMult = $this->service->getMultiplier('b2c', 'facebook');
        $this->assertGreaterThanOrEqual($b2cMult, $b2bMult);
    }

    public function test_get_multiplier_default_for_unknown_segment()
    {
        $multiplier = $this->service->getMultiplier('unknown_segment');
        $this->assertEquals(1.0, $multiplier);
    }

    public function test_get_multiplier_for_all_platforms()
    {
        $platforms = ['facebook', 'tiktok', 'google', 'snapchat'];
        foreach ($platforms as $platform) {
            $multiplier = $this->service->getMultiplier('b2b', $platform);
            $this->assertIsFloat($multiplier);
            $this->assertGreaterThan(0, $multiplier);
        }
    }

    public function test_apply_multiplier_returns_array()
    {
        $result = $this->service->applyMultiplier(100, 'b2b', 'facebook');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('original_value', $result);
        $this->assertArrayHasKey('adjusted_value', $result);
        $this->assertArrayHasKey('multiplier', $result);
        $this->assertArrayHasKey('segment', $result);
        $this->assertGreaterThan(100, $result['adjusted_value']);
    }

    public function test_apply_multiplier_for_b2c()
    {
        $result = $this->service->applyMultiplier(100, 'b2c', 'facebook');
        $this->assertEquals(100.0, $result['original_value']);
        $this->assertEquals('b2c', $result['segment']);
    }

    public function test_adjust_event_payload()
    {
        $payload = [
            'platform' => 'facebook',
            'data' => ['value' => 200],
        ];
        $result = $this->service->adjustEventPayload($payload, 'b2b');
        $this->assertArrayHasKey('data', $result);
        $this->assertGreaterThan(200, $result['data']['value']);
        $this->assertEquals(200, $result['data']['_original_value']);
    }

    public function test_adjust_event_payload_zero_value()
    {
        $payload = [
            'platform' => 'facebook',
            'data' => ['value' => 0],
        ];
        $result = $this->service->adjustEventPayload($payload, 'b2b');
        $this->assertEquals(0, $result['data']['value']);
    }

    public function test_predict_ltv_returns_array()
    {
        $result = $this->service->predictLtv(150, 0);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('segment', $result);
        $this->assertArrayHasKey('ltv_30d', $result);
    }
}
