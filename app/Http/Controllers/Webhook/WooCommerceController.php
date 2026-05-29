<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\WooCommerceService;
use App\Services\AdvertisingTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WooCommerceController extends Controller
{
    private WooCommerceService $woocommerce;
    private AdvertisingTrackingService $tracking;

    public function __construct(WooCommerceService $woocommerce, AdvertisingTrackingService $tracking)
    {
        $this->woocommerce = $woocommerce;
        $this->tracking = $tracking;
    }

    public function handle(Request $request)
    {
        $rawBody = $request->getContent();
        $signature = $request->header('X-WC-Webhook-Signature');
        $topic = $request->header('X-WC-Webhook-Topic', 'unknown');

        if (!$this->woocommerce->verifyWebhook($signature, $rawBody)) {
            Log::warning('WooCommerce: Invalid webhook signature', ['topic' => $topic]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();

        try {
            $processed = match (true) {
                str_starts_with($topic, 'order.created'),
                str_starts_with($topic, 'order.status_changed') && ($payload['status'] ?? '') === 'completed'
                    => $this->handleOrderCreated($payload),
                str_starts_with($topic, 'order.updated') => $this->handleOrderUpdated($payload),
                str_starts_with($topic, 'order.deleted') => $this->handleOrderDeleted($payload),
                str_starts_with($topic, 'customer.created') => $this->handleCustomerCreated($payload),
                str_starts_with($topic, 'product.created'),
                str_starts_with($topic, 'product.updated') => $this->handleProduct($payload),
                default => false,
            };

            $this->woocommerce->logWebhook($topic, $payload, $processed);
        } catch (\Exception $e) {
            Log::error('WooCommerce webhook error', ['topic' => $topic, 'error' => $e->getMessage()]);
            $this->woocommerce->logWebhook($topic, $payload, false, $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleOrderCreated(array $payload): bool
    {
        $orderData = $this->woocommerce->extractOrderData($payload);

        $this->tracking->trackEvent('Purchase', [
            'value' => $orderData['total'],
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
            'zip' => $orderData['postcode'],
        ], $orderData['source']);

        return true;
    }

    private function handleOrderUpdated(array $payload): bool
    {
        $orderData = $this->woocommerce->extractOrderData($payload);

        $this->tracking->trackEvent('CustomEvent', [
            'event' => 'woocommerce_order_updated',
            'order_id' => $orderData['order_id'],
            'status' => $orderData['status'],
            'value' => $orderData['total'],
            'currency' => $orderData['currency'],
        ], ['email' => $orderData['email']], $orderData['source']);

        return true;
    }

    private function handleOrderDeleted(array $payload): bool
    {
        $orderData = $this->woocommerce->extractOrderData($payload);

        $this->tracking->trackEvent('CustomEvent', [
            'event' => 'woocommerce_order_deleted',
            'order_id' => $orderData['order_id'],
            'value' => $orderData['total'],
        ], [], $orderData['source']);

        return true;
    }

    private function handleCustomerCreated(array $payload): bool
    {
        $billing = $payload['billing'] ?? [];

        $this->tracking->trackEvent('Lead', [
            'event' => 'woocommerce_customer_registered',
            'customer_id' => $payload['id'] ?? null,
        ], [
            'email' => $payload['email'] ?? $billing['email'] ?? '',
            'phone' => $billing['phone'] ?? '',
            'first_name' => $payload['first_name'] ?? $billing['first_name'] ?? '',
            'last_name' => $payload['last_name'] ?? $billing['last_name'] ?? '',
        ], 'woocommerce');

        return true;
    }

    private function handleProduct(array $payload): bool
    {
        $this->tracking->trackEvent('CustomEvent', [
            'event' => 'woocommerce_product_sync',
            'product_id' => (string) $payload['id'],
            'sku' => $payload['sku'] ?? '',
            'name' => $payload['name'] ?? '',
            'price' => (float) ($payload['price'] ?? 0),
        ], [], 'woocommerce');

        return true;
    }
}
