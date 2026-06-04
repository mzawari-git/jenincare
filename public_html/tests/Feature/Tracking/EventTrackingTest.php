<?php

namespace Tests\Feature\Tracking;

use App\Models\MarketingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_event_requires_event_name()
    {
        MarketingSetting::set('custom_api_enabled', true);
        $response = $this->postJson('/api/tracking/event', [
            'event_data' => ['value' => 100],
        ]);
        $response->assertStatus(422);
    }

    public function test_tracking_event_cannot_be_empty()
    {
        MarketingSetting::set('custom_api_enabled', true);
        $response = $this->postJson('/api/tracking/event', []);
        $response->assertStatus(422);
    }

    public function test_tracking_batch_requires_event_name()
    {
        MarketingSetting::set('custom_api_enabled', true);
        $response = $this->postJson('/api/tracking/batch', [
            'events' => [
                ['custom_data' => ['value' => 50]],
            ],
        ]);
        $response->assertStatus(422);
    }

    public function test_behavioral_endpoint_accepts_valid_data()
    {
        $response = $this->postJson('/api/track/behavior', [
            'bot_score' => 25,
            'time_on_page' => 12000,
            'scroll_depth' => 75,
            'click_count' => 5,
            'mouse_distance' => 450,
            'keypress_count' => 8,
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_fingerprint_without_cookie_still_works()
    {
        $response = $this->postJson('/api/track/fingerprint', [
            'fingerprint_hash' => 'sha256def456',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_tracking_health_endpoint()
    {
        $response = $this->getJson('/api/tracking/health');
        $response->assertSuccessful();
    }
}
