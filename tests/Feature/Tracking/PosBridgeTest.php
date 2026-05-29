<?php

namespace Tests\Feature\Tracking;

use App\Models\MarketingSetting;
use App\Models\PosSale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosBridgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_sale_requires_pos_sale_id()
    {
        $response = $this->postJson('/api/pos/sale', []);
        $response->assertStatus(422);
    }

    public function test_pos_sale_creates_successfully()
    {
        $response = $this->postJson('/api/pos/sale', [
            'pos_sale_id' => 'POS-001',
            'order_total' => 150.00,
            'customer_email' => 'customer@example.com',
            'currency' => 'ILS',
        ]);
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_pos_sale_rejects_duplicates()
    {
        $this->postJson('/api/pos/sale', [
            'pos_sale_id' => 'POS-DUP',
            'order_total' => 100,
        ]);

        $response = $this->postJson('/api/pos/sale', [
            'pos_sale_id' => 'POS-DUP',
            'order_total' => 100,
        ]);
        $response->assertStatus(409);
    }

    public function test_pos_stats_returns_structure()
    {
        $response = $this->getJson('/api/pos/stats?days=30');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_sales', 'total_revenue', 'matched_sales', 'match_rate',
        ]);
    }
}
