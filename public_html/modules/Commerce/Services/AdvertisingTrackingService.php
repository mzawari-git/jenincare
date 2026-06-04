<?php

namespace Modules\Commerce\Services;

class AdvertisingTrackingService
{
    public function trackViewContent($product) { return true; }
    public function trackAddToCart($product, $quantity = 1) { return true; }
    public function trackPurchase($order) { return true; }
}