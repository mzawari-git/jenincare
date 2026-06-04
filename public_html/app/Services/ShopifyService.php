<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    private ?string $shopDomain;
    private ?string $accessToken;
    private ?string $apiSecret;
    private bool $enabled;
    private string $apiVersion;

    public function __construct()
    {
        $this->shopDomain = \App\Models\MarketingSetting::get('shopify_shop_domain');
        $this->accessToken = \App\Models\MarketingSetting::get('shopify_access_token');
        $this->apiSecret = \App\Models\MarketingSetting::get('shopify_api_secret');
        $this->enabled = (bool) \App\Models\MarketingSetting::get('shopify_enabled', false);
        $this->apiVersion = '2024-10';
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->shopDomain && $this->accessToken;
    }

    public function verifyWebhook(string $hmacHeader, string $rawBody): bool
    {
        if (!$this->apiSecret) {
            Log::warning('Shopify: No API secret configured for HMAC verification');
            return false;
        }

        $calculated = base64_encode(hash_hmac('sha256', $rawBody, $this->apiSecret, true));
        return hash_equals($calculated, $hmacHeader);
    }

    public function getOrders(array $params = []): ?array
    {
        if (!$this->isEnabled()) return null;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
            'Content-Type' => 'application/json',
        ])->get("https://{$this->shopDomain}/admin/api/{$this->apiVersion}/orders.json", $params);

        if ($response->failed()) {
            Log::error('Shopify API: Failed to fetch orders', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json();
    }

    public function getOrder(int $orderId): ?array
    {
        if (!$this->isEnabled()) return null;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
        ])->get("https://{$this->shopDomain}/admin/api/{$this->apiVersion}/orders/{$orderId}.json");

        if ($response->failed()) return null;

        return $response->json();
    }

    public function getProducts(array $params = []): ?array
    {
        if (!$this->isEnabled()) return null;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
        ])->get("https://{$this->shopDomain}/admin/api/{$this->apiVersion}/products.json", $params);

        if ($response->failed()) return null;

        return $response->json();
    }

    public function getCustomers(array $params = []): ?array
    {
        if (!$this->isEnabled()) return null;

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
        ])->get("https://{$this->shopDomain}/admin/api/{$this->apiVersion}/customers.json", $params);

        if ($response->failed()) return null;

        return $response->json();
    }

    public function extractOrderData(array $order): array
    {
        $billingAddress = $order['billing_address'] ?? [];
        $shippingAddress = $order['shipping_address'] ?? [];
        $customer = $order['customer'] ?? [];

        return [
            'order_id' => (string) $order['id'],
            'order_name' => $order['name'] ?? '',
            'order_number' => $order['order_number'] ?? null,
            'email' => $customer['email'] ?? $billingAddress['email'] ?? '',
            'phone' => $customer['phone'] ?? $billingAddress['phone'] ?? '',
            'first_name' => $customer['first_name'] ?? $billingAddress['first_name'] ?? '',
            'last_name' => $customer['last_name'] ?? $billingAddress['last_name'] ?? '',
            'city' => $billingAddress['city'] ?? '',
            'country' => $billingAddress['country_code'] ?? '',
            'zip' => $billingAddress['zip'] ?? '',
            'total_price' => (float) ($order['total_price'] ?? 0),
            'subtotal_price' => (float) ($order['subtotal_price'] ?? 0),
            'total_discounts' => (float) ($order['total_discounts'] ?? 0),
            'total_tax' => (float) ($order['total_tax'] ?? 0),
            'shipping_price' => (float) ($order['total_shipping_price_set']['shop_money']['amount'] ?? 0),
            'currency' => $order['currency'] ?? 'SAR',
            'financial_status' => $order['financial_status'] ?? '',
            'fulfillment_status' => $order['fulfillment_status'] ?? '',
            'created_at' => $order['created_at'] ?? '',
            'line_items' => array_map(fn($item) => [
                'id' => (string) $item['id'],
                'product_id' => (string) ($item['product_id'] ?? ''),
                'variant_id' => (string) ($item['variant_id'] ?? ''),
                'title' => $item['title'] ?? '',
                'sku' => $item['sku'] ?? '',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price' => (float) ($item['price'] ?? 0),
                'total' => (float) ($item['quantity'] ?? 1) * (float) ($item['price'] ?? 0),
            ], $order['line_items'] ?? []),
            'source' => 'shopify',
        ];
    }

    public function logWebhook(string $topic, array $payload, bool $processed, ?string $error = null): void
    {
        try {
            \App\Models\CapiEventLog::create([
                'platform' => 'shopify',
                'event_name' => $topic,
                'event_id' => $payload['id'] ?? uniqid('shopify_', true),
                'status' => $processed ? 'processed' : 'failed',
                'response_data' => $error ? ['error' => $error] : [],
                'ip_address' => request()->ip() ?? '0.0.0.0',
                'session_id' => $payload['id'] ?? null,
                'raw_payload_preview' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Exception $e) {
            Log::error('Shopify: Failed to log webhook', ['error' => $e->getMessage()]);
        }
    }

    public static function getWebhookTopics(): array
    {
        return [
            'orders/create',
            'orders/updated',
            'orders/paid',
            'orders/fulfilled',
            'orders/cancelled',
            'carts/create',
            'carts/update',
            'checkouts/create',
            'checkouts/update',
            'app/uninstalled',
            'shop/update',
        ];
    }
}
