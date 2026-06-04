<?php

namespace Modules\Commerce\Services;

class OrderProcessor
{
    public function process($orderId) { return true; }
    public function calculateTotals($items) { return ['subtotal' => 0, 'tax' => 0, 'total' => 0]; }
}