<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class IdentityService
{
    const COOKIE_NAME = '_juuid';
    const COOKIE_EXPIRY_DAYS = 400;

    public function getOrCreateUuid(Request $request): string
    {
        $uuid = $request->cookie(self::COOKIE_NAME);

        if ($uuid && $this->isValidUuid($uuid)) {
            return $uuid;
        }

        $uuid = $request->header('X-UUID');

        if ($uuid && $this->isValidUuid($uuid)) {
            return $uuid;
        }

        $uuid = (string) Str::uuid();

        cookie()->queue(
            Cookie::make(
                self::COOKIE_NAME,
                $uuid,
                self::COOKIE_EXPIRY_DAYS * 1440,
                '/',
                null,
                config('app.env') === 'production',
                true,
                false,
                'strict'
            )
        );

        return $uuid;
    }

    public function getIdentity(Request $request): array
    {
        $uuid = $this->getOrCreateUuid($request);

        $identity = [
            'uuid' => $uuid,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('referer'),
            'url' => $request->fullUrl(),
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_term' => $request->query('utm_term'),
            'utm_content' => $request->query('utm_content'),
            'fbclid' => $request->query('fbclid'),
            'gclid' => $request->query('gclid'),
            'ttclid' => $request->query('ttclid'),
            'twclid' => $request->query('twclid'),
        ];

        if ($request->user()) {
            $identity['user_id'] = $request->user()->id;
            $identity['email_hash'] = $request->user()->email ? sha1($request->user()->email) : null;
            $identity['phone_hash'] = $request->user()->phone ? sha1($request->user()->phone) : null;
        }

        return $identity;
    }

    public function mergeOnLogin(User $user, string $anonymousUuid): void
    {
        $existing = $user->identity_uuid;

        if ($existing && $existing !== $anonymousUuid) {
            $this->logIdentityMerge($anonymousUuid, $existing, $user->id);
            return;
        }

        if (!$existing) {
            $user->identity_uuid = $anonymousUuid;
            $user->save();
        }
    }

    public function isValidUuid(string $uuid): bool
    {
        return Str::isUuid($uuid);
    }

    private function logIdentityMerge(string $from, string $to, int $userId): void
    {
        logger()->info('Identity merged', [
            'from_uuid' => $from,
            'to_uuid' => $to,
            'user_id' => $userId,
        ]);
    }
}
