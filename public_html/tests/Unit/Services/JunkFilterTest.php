<?php

namespace Tests\Unit\Services;

use App\Services\Sanitization\JunkFilter;
use Tests\TestCase;

class JunkFilterTest extends TestCase
{
    private JunkFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = app(JunkFilter::class);
    }

    public function test_get_name_returns_string()
    {
        $this->assertIsString($this->filter->getName());
    }

    public function test_process_returns_array()
    {
        $result = $this->filter->process(['data' => ['email' => 'real@example.com']]);
        $this->assertIsArray($result);
    }

    public function test_test_email_blocked()
    {
        $result = $this->filter->process(['data' => ['email' => 'test@test.com']]);
        $this->assertTrue($result['_blocked'] ?? false);
    }

    public function test_yopmail_blocked()
    {
        $result = $this->filter->process(['data' => ['email' => 'user@yopmail.com']]);
        $this->assertTrue($result['_blocked'] ?? false);
    }

    public function test_mailinator_blocked()
    {
        $result = $this->filter->process(['data' => ['email' => 'user@mailinator.com']]);
        $this->assertTrue($result['_blocked'] ?? false);
    }

    public function test_real_email_allowed()
    {
        $result = $this->filter->process(['data' => ['email' => 'real.person@gmail.com']]);
        $this->assertFalse($result['_blocked'] ?? false);
    }

    public function test_test_in_product_name_blocked()
    {
        $result = $this->filter->process(['data' => ['product_name' => 'test order']]);
        $this->assertTrue($result['_blocked'] ?? false);
    }

    public function test_real_product_name_allowed()
    {
        $result = $this->filter->process(['data' => ['product_name' => 'Quality Face Cream']]);
        $this->assertFalse($result['_blocked'] ?? false);
    }

    public function test_empty_payload_allowed()
    {
        $result = $this->filter->process(['data' => []]);
        $this->assertFalse($result['_blocked'] ?? false);
    }

    public function test_implements_interface()
    {
        $this->assertInstanceOf(\App\Services\Sanitization\SanitizationStepInterface::class, $this->filter);
    }
}
