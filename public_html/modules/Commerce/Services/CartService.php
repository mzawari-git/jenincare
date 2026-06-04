<?php

namespace Modules\Commerce\Services;

class CartService
{
    public function getCart() { return ['items' => []]; }
    public function addItem($productId, $quantity = 1) { return true; }
    public function removeItem($productId) { return true; }
    public function clear() { return true; }
}