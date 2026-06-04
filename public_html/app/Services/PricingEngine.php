<?php

namespace App\Services;

use Modules\Commerce\Models\Product;
use Modules\B2B\Models\Company;
use App\Models\Setting;

class PricingEngine
{
    private const DEFAULT_TAX_RATE = 0.15;

    private function getDefaultShippingCost(): float
    {
        $setting = Setting::where('key', 'shipping_cost')->first();
        return $setting && is_numeric($setting->value) ? floatval($setting->value) : 25.00;
    }

    private function getFreeShippingThreshold(): float
    {
        $setting = Setting::where('key', 'free_shipping_min')->first();
        if (!$setting) {
            $setting = Setting::where('key', 'free_shipping_threshold')->first();
        }
        return $setting && is_numeric($setting->value) ? floatval($setting->value) : 200.00;
    }

    public function calculateB2CPrice(Product $product, int $quantity = 1): array
    {
        $unitPrice = $product->final_b2c_price;
        $subtotal = $unitPrice * $quantity;

        $discountAmount = 0;
        $discountPercentage = 0;

        if ($product->isDiscountActive()) {
            if ($product->discount_percentage > 0) {
                $discountPercentage = $product->discount_percentage;
                $discountAmount = $subtotal * ($discountPercentage / 100);
            } elseif ($product->discount_amount > 0) {
                $discountAmount = $product->discount_amount * $quantity;
            }
        }

        $afterDiscount = $subtotal - $discountAmount;

        $shippingCost = $this->calculateShipping($afterDiscount, $product->free_shipping, $product->shipping_cost);

        $taxAmount = $this->calculateTax($afterDiscount);

        $total = $afterDiscount + $shippingCost + $taxAmount;

        return [
            'unit_price' => round($unitPrice, 2),
            'quantity' => $quantity,
            'subtotal' => round($subtotal, 2),
            'discount_type' => $product->discount_percentage > 0 ? 'percentage' : 'fixed',
            'discount_percentage' => $discountPercentage,
            'discount_amount' => round($discountAmount, 2),
            'after_discount' => round($afterDiscount, 2),
            'shipping_cost' => round($shippingCost, 2),
            'free_shipping' => $shippingCost == 0,
            'tax_rate' => self::DEFAULT_TAX_RATE,
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
            'currency' => 'ILS',
        ];
    }

    public function calculateB2BPrice(Product $product, int $quantity, ?Company $company = null): array
    {
        $tierPrice = $product->getB2bPriceForQuantity($quantity);
        $tier = $product->getB2bTierForQuantity($quantity);

        $subtotal = $tierPrice * $quantity;

        $discountAmount = 0;
        $discountPercentage = 0;

        if ($company) {
            $categoryDiscount = $company->getDiscountForCategory($product->category?->name_ar);
            if ($categoryDiscount > 0) {
                $discountPercentage = $categoryDiscount;
                $discountAmount = $subtotal * ($discountPercentage / 100);
            }
        }

        $afterDiscount = $subtotal - $discountAmount;

        $shippingCost = 0;
        if (!$company?->has_free_shipping) {
            $shippingCost = $this->calculateB2BShipping($afterDiscount);
        }

        $taxAmount = $this->calculateTax($afterDiscount);

        $total = $afterDiscount + $shippingCost + $taxAmount;

        $minQuantity = $product->b2b_min_quantity;
        $meetsMinimum = $quantity >= $minQuantity;

        return [
            'unit_price' => round($tierPrice, 2),
            'quantity' => $quantity,
            'tier' => $tier,
            'subtotal' => round($subtotal, 2),
            'discount_type' => 'percentage',
            'discount_percentage' => $discountPercentage,
            'discount_amount' => round($discountAmount, 2),
            'after_discount' => round($afterDiscount, 2),
            'shipping_cost' => round($shippingCost, 2),
            'free_shipping' => $shippingCost == 0,
            'tax_rate' => self::DEFAULT_TAX_RATE,
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
            'currency' => 'ILS',
            'meets_minimum' => $meetsMinimum,
            'minimum_quantity' => $minQuantity,
        ];
    }

    public function calculateShipping(float $subtotal, bool $productFreeShipping = false, float $productShippingCost = 0): float
    {
        if ($productFreeShipping || $subtotal >= $this->getFreeShippingThreshold()) {
            return 0;
        }

        return $productShippingCost > 0 ? $productShippingCost : $this->getDefaultShippingCost();
    }

    private function calculateB2BShipping(float $subtotal): float
    {
        if ($subtotal >= 500) {
            return 0;
        }

        if ($subtotal >= 200) {
            return 15;
        }

        return $this->getDefaultShippingCost();
    }

    public function calculateTax(float $amount, ?float $rate = null): float
    {
        $taxRate = $rate ?? self::DEFAULT_TAX_RATE;
        return round($amount * $taxRate, 2);
    }

    public function applyCoupon(array $cartData, string $couponCode): array
    {
        $coupon = $this->getCoupon($couponCode);

        if (!$coupon) {
            return array_merge($cartData, [
                'coupon_error' => 'كود الخصم غير صحيح أو منتهي الصلاحية',
                'coupon_applied' => false,
            ]);
        }

        $discountAmount = 0;

        if ($coupon['type'] === 'percentage') {
            $discountAmount = $cartData['subtotal'] * ($coupon['value'] / 100);
            if ($coupon['max_discount']) {
                $discountAmount = min($discountAmount, $coupon['max_discount']);
            }
        } else {
            $discountAmount = $coupon['value'];
        }

        if ($cartData['subtotal'] < $coupon['min_order']) {
            return array_merge($cartData, [
                'coupon_error' => 'الحد الأدنى للطلب ' . $coupon['min_order'] . ' ILS',
                'coupon_applied' => false,
            ]);
        }

        $newSubtotal = $cartData['subtotal'] - $discountAmount;
        $newTax = $this->calculateTax($newSubtotal);
        $newShipping = $this->calculateShipping($newSubtotal);
        $newTotal = $newSubtotal + $newTax + $newShipping;

        return array_merge($cartData, [
            'coupon_code' => $couponCode,
            'coupon_discount' => round($discountAmount, 2),
            'coupon_applied' => true,
            'coupon_error' => null,
            'subtotal' => round($newSubtotal, 2),
            'tax_amount' => round($newTax, 2),
            'shipping_cost' => round($newShipping, 2),
            'total' => round($newTotal, 2),
        ]);
    }

    private function getCoupon(string $code): ?array
    {
        $coupons = [
            'WELCOME10' => [
                'type' => 'percentage',
                'value' => 10,
                'max_discount' => 50,
                'min_order' => 100,
            ],
            'SAVE20' => [
                'type' => 'fixed',
                'value' => 20,
                'max_discount' => null,
                'min_order' => 50,
            ],
        ];

        return $coupons[$code] ?? null;
    }

    public function calculateCartTotals(array $items, ?string $couponCode = null, ?Company $company = null): array
    {
        $subtotal = 0;
        $totalDiscount = 0;
        $itemsData = [];

        foreach ($items as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];

            if ($company) {
                $priceData = $this->calculateB2BPrice($product, $quantity, $company);
            } else {
                $priceData = $this->calculateB2CPrice($product, $quantity);
            }

            $subtotal += $priceData['subtotal'];
            $totalDiscount += $priceData['discount_amount'];

            $itemsData[] = array_merge($priceData, [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => $product->main_image_url,
            ]);
        }

        $shippingCost = $this->calculateShipping($subtotal);
        $taxAmount = $this->calculateTax($subtotal - $totalDiscount);
        $total = $subtotal + $shippingCost + $taxAmount - $totalDiscount;

        $cartData = [
            'items' => $itemsData,
            'items_count' => count($items),
            'total_quantity' => array_sum(array_column($items, 'quantity')),
            'subtotal' => round($subtotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'shipping_cost' => round($shippingCost, 2),
            'tax_rate' => self::DEFAULT_TAX_RATE,
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
            'currency' => 'ILS',
            'coupon_applied' => false,
        ];

        if ($couponCode) {
            $cartData = $this->applyCoupon($cartData, $couponCode);
        }

        return $cartData;
    }
}
