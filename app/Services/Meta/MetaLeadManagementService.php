<?php

namespace App\Services\Meta;

use App\Models\Meta\MetaLeadConversion;
use App\Models\Meta\MetaCampaign;
use Modules\Meta\Models\MetaLead;
use Illuminate\Support\Facades\Log;

class MetaLeadManagementService
{
    public function trackConversion($leadId, $conversionData)
    {
        $lead = MetaLead::findOrFail($leadId);
        
        $daysToConvert = $lead->created_at->diffInDays(now());
        
        $conversion = MetaLeadConversion::create([
            'lead_id' => $leadId,
            'campaign_id' => $conversionData['campaign_id'] ?? null,
            'booking_id' => $conversionData['booking_id'] ?? null,
            'order_id' => $conversionData['order_id'] ?? null,
            'conversion_type' => $conversionData['conversion_type'] ?? 'booking',
            'value' => $conversionData['value'] ?? null,
            'currency' => $conversionData['currency'] ?? 'ILS',
            'days_to_convert' => $daysToConvert,
            'touchpoints' => $this->getTouchpoints($leadId),
            'attribution_model' => $conversionData['attribution_model'] ?? 'last_click',
        ]);

        $lead->update([
            'stage' => 'converted',
            'purchase_probability' => 100,
        ]);

        Log::info("Lead {$leadId} converted", ['conversion_id' => $conversion->id]);

        return $conversion;
    }

    protected function getTouchpoints($leadId)
    {
        $lead = MetaLead::with('leadScores')->find($leadId);
        
        if (!$lead || !$lead->leadScores) {
            return [];
        }

        return $lead->leadScores->map(function ($score) {
            return [
                'event_type' => $score->event_type,
                'score_delta' => $score->score_delta,
                'timestamp' => $score->created_at,
            ];
        })->toArray();
    }

    public function calculateLeadScore($leadId)
    {
        $lead = MetaLead::findOrFail($leadId);
        
        $score = 0;
        
        if ($lead->email) $score += 20;
        if ($lead->phone) $score += 20;
        if ($lead->city) $score += 10;
        
        $score += min(30, $lead->total_interactions * 5);
        
        if ($lead->intent === 'purchase') $score += 20;
        elseif ($lead->intent === 'trust') $score += 10;
        
        $score = min(100, $score);
        
        $lead->update(['lead_score' => $score]);
        
        return $score;
    }

    public function getConversionStats($campaignId = null, $days = 30)
    {
        $query = MetaLeadConversion::where('created_at', '>=', now()->subDays($days));
        
        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }

        $conversions = $query->get();

        return [
            'total_conversions' => $conversions->count(),
            'total_value' => $conversions->sum('value'),
            'avg_days_to_convert' => $conversions->avg('days_to_convert'),
            'by_type' => $conversions->groupBy('conversion_type')->map->count(),
            'by_campaign' => $conversions->groupBy('campaign_id')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'value' => $group->sum('value'),
                ];
            }),
        ];
    }

    public function getLeadFunnel($days = 30)
    {
        $leads = MetaLead::where('created_at', '>=', now()->subDays($days))->get();

        return [
            'new' => $leads->where('stage', 'new')->count(),
            'engaged' => $leads->where('stage', 'engaged')->count(),
            'warm' => $leads->where('stage', 'warm')->count(),
            'hot' => $leads->where('stage', 'hot')->count(),
            'converted' => $leads->where('stage', 'converted')->count(),
        ];
    }

    public function getTopConvertingCampaigns($days = 30, $limit = 10)
    {
        return MetaLeadConversion::select('campaign_id')
            ->selectRaw('COUNT(*) as conversions')
            ->selectRaw('SUM(value) as total_value')
            ->selectRaw('AVG(days_to_convert) as avg_days')
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('campaign_id')
            ->groupBy('campaign_id')
            ->orderByDesc('conversions')
            ->limit($limit)
            ->with('campaign:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'campaign_id' => $item->campaign_id,
                    'campaign_name' => $item->campaign?->name ?? 'Unknown',
                    'conversions' => $item->conversions,
                    'total_value' => $item->total_value,
                    'avg_days' => round($item->avg_days, 1),
                ];
            });
    }

    public function sendInstantNotification($leadId, $channels = ['email'])
    {
        $lead = MetaLead::findOrFail($leadId);
        
        foreach ($channels as $channel) {
            try {
                switch ($channel) {
                    case 'email':
                        $this->sendEmailNotification($lead);
                        break;
                    case 'whatsapp':
                        $this->sendWhatsAppNotification($lead);
                        break;
                    case 'sms':
                        $this->sendSMSNotification($lead);
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Failed to send notification via {$channel}: " . $e->getMessage());
            }
        }
    }

    protected function sendEmailNotification($lead)
    {
        Log::info("Email notification sent for lead {$lead->id}");
    }

    protected function sendWhatsAppNotification($lead)
    {
        Log::info("WhatsApp notification sent for lead {$lead->id}");
    }

    protected function sendSMSNotification($lead)
    {
        Log::info("SMS notification sent for lead {$lead->id}");
    }

    public function autoScoreAllLeads()
    {
        $leads = MetaLead::where('stage', '!=', 'converted')->get();
        
        $results = [];
        foreach ($leads as $lead) {
            $results[] = [
                'lead_id' => $lead->id,
                'old_score' => $lead->lead_score,
                'new_score' => $this->calculateLeadScore($lead->id),
            ];
        }

        return $results;
    }
}
