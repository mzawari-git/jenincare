<?php

namespace Tests\Feature\Tracking;

use App\Models\MarketingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_health_returns_ok()
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
    }

    public function test_tracking_health_returns_ok()
    {
        MarketingSetting::set('custom_api_enabled', true);
        $response = $this->getJson('/api/tracking/health');
        $response->assertStatus(200);
    }

    public function test_fingerprint_endpoint_accepts_data()
    {
        $response = $this->withCookie('_juuid', 'test-uuid-123')
            ->postJson('/api/track/fingerprint', [
                'fingerprint_hash' => 'abc123',
                'fingerprint_data' => ['canvas' => 'test'],
            ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_behavioral_endpoint_accepts_data()
    {
        $response = $this->postJson('/api/track/behavior', [
            'bot_score' => 10,
            'time_on_page' => 5000,
            'scroll_depth' => 50,
            'click_count' => 3,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_tracking_event_validates_request()
    {
        MarketingSetting::set('custom_api_enabled', true);
        $response = $this->postJson('/api/tracking/event', []);
        $response->assertStatus(422);
    }
}
