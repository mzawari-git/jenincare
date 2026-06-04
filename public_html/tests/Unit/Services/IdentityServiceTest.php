<?php

namespace Tests\Unit\Services;

use App\Services\IdentityService;
use Illuminate\Http\Request;
use Tests\TestCase;

class IdentityServiceTest extends TestCase
{
    private IdentityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new IdentityService();
    }

    public function test_generates_uuid_on_first_request()
    {
        $request = Request::create('/test', 'GET');
        $uuid = $this->service->getOrCreateUuid($request);
        $this->assertNotNull($uuid);
        $this->assertTrue($this->service->isValidUuid($uuid));
    }

    public function test_returns_same_uuid_from_cookie()
    {
        $existingUuid = '550e8400-e29b-41d4-a716-446655440000';
        $request = Request::create('/test', 'GET', [], ['_juuid' => $existingUuid]);
        $uuid = $this->service->getOrCreateUuid($request);
        $this->assertEquals($existingUuid, $uuid);
    }

    public function test_returns_same_uuid_from_header()
    {
        $existingUuid = '550e8400-e29b-41d4-a716-446655440001';
        $request = Request::create('/test', 'GET');
        $request->headers->set('X-UUID', $existingUuid);
        $uuid = $this->service->getOrCreateUuid($request);
        $this->assertEquals($existingUuid, $uuid);
    }

    public function test_is_valid_uuid()
    {
        $this->assertTrue($this->service->isValidUuid('550e8400-e29b-41d4-a716-446655440000'));
        $this->assertFalse($this->service->isValidUuid('not-a-uuid'));
        $this->assertFalse($this->service->isValidUuid(''));
    }

    public function test_identity_contains_utm_params()
    {
        $request = Request::create('/test', 'GET', [
            'utm_source' => 'facebook',
            'utm_campaign' => 'summer_sale',
            'fbclid' => 'abc123',
        ]);
        $identity = $this->service->getIdentity($request);
        $this->assertEquals('facebook', $identity['utm_source']);
        $this->assertEquals('summer_sale', $identity['utm_campaign']);
        $this->assertEquals('abc123', $identity['fbclid']);
    }
}
