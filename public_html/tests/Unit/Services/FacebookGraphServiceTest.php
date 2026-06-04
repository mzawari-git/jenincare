<?php

namespace Tests\Unit\Services;

use App\Services\Meta\FacebookGraphService;
use Tests\TestCase;

class FacebookGraphServiceTest extends TestCase
{
    private FacebookGraphService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FacebookGraphService::class);
    }

    public function test_get_graph_url_returns_valid_url()
    {
        $url = $this->service->getGraphUrl();
        $this->assertStringContainsString('graph.facebook.com', $url);
        $this->assertStringContainsString('v', $url);
    }

    public function test_set_user_access_token_returns_self()
    {
        $result = $this->service->setUserAccessToken('test-token');
        $this->assertSame($this->service, $result);
    }

    public function test_get_app_id_returns_string()
    {
        $this->assertIsString(config('meta.app_id'));
    }

    public function test_get_api_version_returns_string()
    {
        $this->assertIsString(config('meta.api_version'));
    }
}
