<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\ShopifyService;
use App\Services\AdvertisingTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyController extends Controller
{
    private ShopifyService $shopify;
    private AdvertisingTrackingService $tracking;

    public function __construct(ShopifyService $shopify, AdvertisingTrackingService $tracking)
    {
        $this->shopify = $shopify;
        $this->tracking = $tracking;
    }

    public function handle(Request $request, string $topic)
    {
        $rawBody = $request->getContent();
        $hmac = $request->header('X-Shopify-Hmac-Sha256');

        if (!$this->shopify->verifyWebhook($hmac, $rawBody)) {
            Log::warning('Shopify: Invalid HMAC signature', ['topic' => $topic]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        try {
            $processed = match ($topic) {
                'orders/create', 'orders/paid' => $this->handleOrderCreated($payload),
                'orders/updated', 'orders/fulfilled' => $this->handleOrderUpdated($payload),
                'orders/cancelled' => $this->handleOrderCancelled($payload),
                'carts/create', 'carts/update' => $this->handleCart($payload),
                'checkouts/create', 'checkouts/update' => $this->handleCheckout($payload),
                'app/uninstalled' => $this->handleUninstalled(),
                default => false,
            };

            $this->shopify->logWebhook($topic, $payload, $processed);
        } catch (\Exception $e) {
            Log::error('Shopify webhook error', ['topic' => $topic, 'error' => $e->getMessage()]);
            $this->shopify->logWebhook($topic, $payload, false, $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleOrderCreated(array $payload): bool
    {
        $orderData = $this->shopify->extractOrderData($payload);

        $this->tracking->trackEvent('Purchase', [
            'value' => $orderData['total_price'],
            'currency' => $orderData['currency'],
            'content_ids' => array_column($orderData['line_items'], 'product_id'),
            'content_type' => 'product',
            'num_items' => array_sum(array_column($orderData['line_items'], 'quantity')),
            'contents' => $orderData['line_items'],
            'order_id' => $orderData['order_id'],
        ], [
            'email' => $orderData['email'],
            'phone' => $orderData['phone'],
            'first_name' => $orderData['first_name'],
            'last_name' => $orderData['last_name'],
            'city' => $orderData['city'],
            'country' => $orderData['country'],
            'zip' => $orderData['zip'],
        ], $orderData['source']);

        return true;
    }

    private function handleOrderUpdated(array $payload): bool
    {
        $orderData = $this->shopify->extractOrderData($payload);

        $this->tracking->trackEvent($orderData['financial_status'] === 'paid' ? 'Purchase' : 'CustomEvent', [
            'value' => $orderData['total_price'],
            'currency' => $orderData['currency'],
            'order_id' => $orderData['order_id'],
            'event' => 'shopify_order_updated',
            'fulfillment_status' => $orderData['fulfillment_status'],
            'financial_status' => $orderData['financial_status'],
        ], [
            'email' => $orderData['email'],
        ], $orderData['source']);

        return true;
    }

    private function handleOrderCancelled(array $payload): bool
    {
        $orderData = $this->shopify->extractOrderData($payload);

        $this->tracking->trackEvent('CustomEvent', [
            'event' => 'shopify_order_cancelled',
            'order_id' => $orderData['order_id'],
            'value' => $orderData['total_price'],
            'currency' => $orderData['currency'],
        ], [
            'email' => $orderData['email'],
        ], $orderData['source']);

        return true;
    }

    private function handleCart(array $payload): bool
    {
        $lineItems = $payload['line_items'] ?? [];
        $total = array_reduce($lineItems, fn($sum, $item) => $sum + (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 1), 0.0);

        $this->tracking->trackEvent('AddToCart', [
            'value' => $total,
            'currency' => $payload['currency'] ?? 'SAR',
            'content_ids' => array_map(fn($i) => (string) ($i['product_id'] ?? $i['variant_id'] ?? ''), $lineItems),
            'content_type' => 'product',
            'num_items' => array_sum(array_column($lineItems, 'quantity')),
        ], [], 'shopify');

        return true;
    }

    private function handleCheckout(array $payload): bool
    {
        $lineItems = $payload['line_items'] ?? [];
        $total = (float) ($payload['total_price'] ?? $payload['total_line_items_price'] ?? 0);
        $email = $payload['email'] ?? $payload['customer']['email'] ?? '';

        $this->tracking->trackEvent('InitiateCheckout', [
            'value' => $total,
            'currency' => $payload['currency'] ?? 'SAR',
            'content_ids' => array_map(fn($i) => (string) ($i['product_id'] ?? ''), $lineItems),
            'content_type' => 'product',
            'num_items' => array_sum(array_column($lineItems, 'quantity')),
        ], ['email' => $email], 'shopify');

        return true;
    }

    private function handleUninstalled(): bool
    {
        \App\Models\MarketingSetting::set('shopify_enabled', false);
        Log::info('Shopify: App uninstalled, disabled connector');
        return true;
    }
}
