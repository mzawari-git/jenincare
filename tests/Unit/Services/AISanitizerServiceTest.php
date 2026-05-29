<?php

namespace Tests\Unit\Services;

use App\Services\AISanitizerService;
use App\Services\AI\OpenAIProvider;
use App\Services\AI\ClaudeProvider;
use App\Services\AI\LlamaProvider;
use Tests\TestCase;

class AISanitizerServiceTest extends TestCase
{
    private AISanitizerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AISanitizerService::class);
    }

    public function test_sanitize_returns_string()
    {
        $result = $this->service->sanitize('Test product name');
        $this->assertIsString($result);
    }

    public function test_sanitize_with_context_returns_string()
    {
        $result = $this->service->sanitize('Test product', ['platform' => 'facebook', 'event' => 'Purchase']);
        $this->assertIsString($result);
    }

    public function test_sanitize_handles_empty_string()
    {
        $result = $this->service->sanitize('');
        $this->assertIsString($result);
    }

    public function test_sanitize_handles_arabic_text()
    {
        $result = $this->service->sanitize('منتج تجريبي للعناية');
        $this->assertIsString($result);
    }
}
