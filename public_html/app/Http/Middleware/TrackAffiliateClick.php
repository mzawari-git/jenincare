<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use Closure;
use Illuminate\Http\Request;

class TrackAffiliateClick
{
    public function handle(Request $request, Closure $next)
    {
        $refCode = $request->query('ref');

        if ($refCode) {
            $affiliate = Affiliate::where('referral_code', $refCode)
                ->where('status', 'active')
                ->first();

            if ($affiliate) {
                session([
                    'affiliate_ref' => $refCode,
                    'affiliate_id' => $affiliate->id,
                    'affiliate_session_id' => session()->getId(),
                ]);

                \App\Models\AffiliateClick::create([
                    'affiliate_id' => $affiliate->id,
                    'session_id' => session()->getId(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_hash' => hash('sha256', $request->ip() . $request->userAgent()),
                    'referral_code' => $refCode,
                    'utm_source' => $request->query('utm_source'),
                    'utm_medium' => $request->query('utm_medium'),
                    'utm_campaign' => $request->query('utm_campaign'),
                    'landing_page' => $request->fullUrl(),
                ]);

                cookie()->queue(
                    'affiliate_ref',
                    $refCode,
                    60 * 24 * 30
                );
            }
        }

        $cookieRef = $request->cookie('affiliate_ref');
        if ($cookieRef && !session('affiliate_ref')) {
            $affiliate = Affiliate::where('referral_code', $cookieRef)
                ->where('status', 'active')
                ->first();
            if ($affiliate) {
                session([
                    'affiliate_ref' => $cookieRef,
                    'affiliate_id' => $affiliate->id,
                ]);
            }
        }

        return $next($request);
    }
}
