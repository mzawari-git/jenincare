<?php

namespace App\Http\Controllers\Api;

use App\Models\Identity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class FingerprintController extends Controller
{
    public function store(Request $request)
    {
        $uuid = $request->cookie('_juuid');

        if (!$uuid || !Str::isUuid($uuid)) {
            $uuid = $request->input('uuid');

            if (!$uuid || !Str::isUuid($uuid)) {
                $uuid = $request->header('X-UUID');
            }
        }

        $data = $request->validate([
            'fingerprint_hash' => 'nullable|string|max:64',
            'fingerprint_data' => 'nullable|array',
            'url' => 'nullable|string',
            'referer' => 'nullable|string',
        ]);

        if ($uuid && Str::isUuid($uuid)) {
            Identity::updateOrCreate(
                ['uuid' => $uuid],
                [
                    'fingerprint_hash' => $data['fingerprint_hash'] ?? null,
                    'fingerprint_data' => $data['fingerprint_data'] ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_seen_at' => now(),
                    'first_seen_at' => Identity::where('uuid', $uuid)->exists()
                        ? null
                        : now(),
                ]
            );
        }

        return response()->json(['success' => true]);
    }
}
