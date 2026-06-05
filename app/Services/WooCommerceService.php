<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WooCommerceService
{
    private ?string $storeUrl = null;
    private ?string $consumerKey = null;
    private ?string $consumerSecret = null;
    private ?bool $enabled = null;
    private bool $loaded = false;

    private function loadSettings(): void
    {
        if ($this->loaded) return;
        try {
            $this->storeUrl = \App\Models\MarketingSetting::get('woocommerce_store_url');
            $this->consumerKey = \App\Models\MarketingSetting::get('woocommerce_consumer_key');
            $this->consumerSecret = \App\Models\MarketingSetting::get('woocommerce_consumer_secret');
            $this->enabled = (bool) \App\Models\MarketingSetting::get('woocommerce_enabled', false);
        } catch (\Exception $e) {
            $this->storeUrl = null;
            $this->consumerKey = null;
            $this->consumerSecret = null;
            $this->enabled = false;
        }
        $this->loaded = true;
    }

    public function __construct()
    {
    }

    public function isEnabled(): bool
    {
        $this->loadSettings();
        return $this->enabled && $this->storeUrl && $this->consumerKey && $this->consumerSecret;
    }

    public function verifyWebhook(string $signatureHeader, string $rawBody): bool
    {
        $this->loadSettings();
        if (!$this->consumerSecret) return false;

        $calculated = base64_encode(hash_hmac('sha256', $rawBody, $this->consumerSecret, true));
        return hash_equals($calculated, $signatureHeader);
    }

    public function getOrders(array $params = []): ?array
    {
        if (!$this->isEnabled()) return null;

        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get(rtrim($this->storeUrl, '/') . '/wp-json/wc/v3/orders', $params);

        if ($response->failed()) {
            Log::error('WooCommerce API: Failed to fetch orders', [
                'status' => $response->status(),
            ]);
            return null;
        }

        return $response->json();
    }

    public function getOrder(int $orderId): ?array
    {
        if (!$this->isEnabled()) return null;

        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get(rtrim($this->storeUrl, '/') . "/wp-json/wc/v3/orders/{$orderId}");

        if ($response->failed()) return null;
        return $response->json();
    }

    public function getProducts(array $params = []): ?array
    {
        if (!$this->isEnabled()) return null;

        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get(rtrim($this->storeUrl, '/') . '/wp-json/wc/v3/products', $params);

        if ($response->failed()) return null;
        return $response->json();
    }

    public function extractOrderData(array $order): array
    {
        $billing = $order['billing'] ?? [];
        $shipping = $order['shipping'] ?? [];

        return [
            'order_id' => (string) $order['id'],
            'order_number' => $order['number'] ?? $order['id'],
            'order_key' => $order['order_key'] ?? '',
            'email' => $billing['email'] ?? '',
            'phone' => $billing['phone'] ?? '',
            'first_name' => $billing['first_name'] ?? '',
            'last_name' => $billing['last_name'] ?? '',
            'company' => $billing['company'] ?? '',
            'address_1' => $billing['address_1'] ?? '',
            'city' => $billing['city'] ?? '',
            'state' => $billing['state'] ?? '',
            'postcode' => $billing['postcode'] ?? '',
            'country' => $billing['country'] ?? '',
            'total' => (float) ($order['total'] ?? 0),
            'subtotal' => (float) ($order['subtotal'] ?? 0),
            'discount_total' => (float) ($order['discount_total'] ?? 0),
            'shipping_total' => (float) ($order['shipping_total'] ?? 0),
            'total_tax' => (float) ($order['total_tax'] ?? 0),
            'currency' => $order['currency'] ?? 'SAR',
            'payment_method' => $order['payment_method'] ?? '',
            'payment_method_title' => $order['payment_method_title'] ?? '',
            'status' => $order['status'] ?? '',
            'date_created' => $order['date_created'] ?? '',
            'customer_id' => $order['customer_id'] ?? 0,
            'customer_note' => $order['customer_note'] ?? '',
            'line_items' => array_map(fn($item) => [
                'id' => (string) $item['id'],
                'product_id' => (string) ($item['product_id'] ?? ''),
                'variation_id' => (string) ($item['variation_id'] ?? ''),
                'name' => $item['name'] ?? '',
                'sku' => $item['sku'] ?? '',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (float) ($item['price'] ?? 0),
                'total' => (float) ($item['total'] ?? 0),
                'subtotal' => (float) ($item['subtotal'] ?? 0),
            ], $order['line_items'] ?? []),
            'source' => 'woocommerce',
        ];
    }

    public function logWebhook(string $topic, array $payload, bool $processed, ?string $error = null): void
    {
        try {
            \App\Models\CapiEventLog::create([
                'platform' => 'woocommerce',
                'event_name' => $topic,
                'event_id' => $payload['id'] ?? uniqid('wc_', true),
                'status' => $processed ? 'processed' : 'failed',
                'response_data' => $error ? ['error' => $error] : [],
                'ip_address' => request()->ip() ?? '0.0.0.0',
                'session_id' => (string) ($payload['id'] ?? ''),
                'raw_payload_preview' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Exception $e) {
            Log::error('WooCommerce: Failed to log webhook', ['error' => $e->getMessage()]);
        }
    }

    public static function getWebhookTopics(): array
    {
        return [
            'order.created',
            'order.updated',
            'order.deleted',
            'order.status_changed',
            'product.created',
            'product.updated',
            'customer.created',
        ];
    }
}
