<?php

namespace Tests\Unit\Services;

use App\Services\MultiPixelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiPixelServiceTest extends TestCase
{
    use RefreshDatabase;

    private MultiPixelService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MultiPixelService::class);
    }

    public function test_get_pixel_ids_returns_array()
    {
        $pixels = $this->service->getPixelIds('facebook');
        $this->assertIsArray($pixels);
    }

    public function test_get_pixel_ids_for_valid_platforms()
    {
        $platforms = ['facebook', 'tiktok', 'google', 'snapchat', 'pinterest', 'twitter', 'linkedin'];
        foreach ($platforms as $platform) {
            $pixels = $this->service->getPixelIds($platform);
            $this->assertIsArray($pixels);
        }
    }

    public function test_get_tokens_returns_array()
    {
        $tokens = $this->service->getTokens('facebook');
        $this->assertIsArray($tokens);
    }

    public function test_get_status_returns_array()
    {
        $status = $this->service->getStatus('facebook');
        $this->assertIsArray($status);
    }

    public function test_fan_out_returns_array()
    {
        $results = $this->service->fanOut('facebook', ['event' => 'test'], function ($pixelId, $events, $index) {
            return ['success' => true, 'pixel_id' => $pixelId];
        });
        $this->assertIsArray($results);
    }
}
