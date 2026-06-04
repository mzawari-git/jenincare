<?php

namespace App\Listeners;

use Modules\Commerce\Models\Order;
use Modules\Meta\Models\MetaLead;
use Modules\Meta\Services\LeadSyncService;

class SyncLeadOnOrder
{
    public function handle($event): void
    {
        try {
            $order = $event->order ?? $event;

            if (!$order instanceof Order) return;

            $lead = MetaLead::where('email', $order->customer_email)
                ->orWhere('phone', $order->customer_phone)
                ->orWhere(function ($q) use ($order) {
                    if ($order->customer_email) $q->where('email', $order->customer_email);
                    if ($order->customer_phone) $q->orWhere('phone', $order->customer_phone);
                })
                ->first();

            if ($lead) {
                $lead->update([
                    'stage' => 'customer',
                    'lead_score' => max($lead->lead_score, 100),
                    'total_interactions' => $lead->total_interactions + 1,
                    'last_activity_at' => now(),
                    'city' => $lead->city ?: $order->shipping_city,
                    'email' => $lead->email ?: $order->customer_email,
                    'phone' => $lead->phone ?: $order->customer_phone,
                    'engagement_type' => 'purchase',
                ]);
            } else {
                MetaLead::create([
                    'sender_name' => $order->customer_name ?? 'Customer',
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                    'city' => $order->shipping_city,
                    'country' => $order->shipping_country ?? 'PS',
                    'source' => 'website',
                    'engagement_type' => 'purchase',
                    'stage' => 'customer',
                    'lead_score' => 100,
                    'purchase_probability' => 100,
                    'total_interactions' => 1,
                    'last_activity_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Silent - don't block order processing
        }
    }
}
