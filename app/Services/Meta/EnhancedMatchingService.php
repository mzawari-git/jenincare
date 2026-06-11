<?php

namespace App\Services\Meta;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class EnhancedMatchingService
{
    public function buildEnhancedUserData(?User $user = null, ?array $requestUserData = null): array
    {
        $data = [];

        $email = $user->email ?? $requestUserData['email'] ?? null;
        if ($email) {
            $data['em'] = hash('sha256', strtolower(trim($email)));
        }

        $phone = $user->phone ?? $requestUserData['phone'] ?? null;
        if ($phone) {
            $data['ph'] = hash('sha256', $this->normalizePhone($phone));
        }

        $name = $user->name ?? $requestUserData['name'] ?? null;
        if ($name) {
            $parts = explode(' ', trim($name), 2);
            $data['fn'] = hash('sha256', strtolower($parts[0]));
            if (!empty($parts[1])) {
                $data['ln'] = hash('sha256', strtolower($parts[1]));
            }
        }

        $city = $requestUserData['city'] ?? null;
        if ($city) {
            $data['ct'] = hash('sha256', strtolower(trim($city)));
        }

        $country = $requestUserData['country'] ?? 'PS';
        if ($country) {
            $data['country'] = hash('sha256', strtolower(trim($country)));
        }

        $zip = $requestUserData['zip'] ?? null;
        if ($zip) {
            $data['zp'] = hash('sha256', trim($zip));
        }

        $gender = $requestUserData['gender'] ?? null;
        if ($gender) {
            $data['ge'] = hash('sha256', strtolower(trim($gender)));
        }

        $birthday = $requestUserData['birthday'] ?? null;
        if ($birthday) {
            $data['db'] = hash('sha256', trim($birthday));
        }

        if ($user) {
            $data['external_id'] = hash('sha256', (string) $user->id);
        } elseif (!empty($requestUserData['external_id'])) {
            $data['external_id'] = hash('sha256', (string) $requestUserData['external_id']);
        }

        $data['client_ip_address'] = request()->ip();
        $data['client_user_agent'] = request()->userAgent();

        $fbp = request()->cookie('_fbp');
        if ($fbp) {
            $data['_fbp'] = $fbp;
        }

        $fbc = request()->cookie('_fbc');
        if ($fbc) {
            $data['_fbc'] = $fbc;
        } else {
            $fbclid = request()->input('fbclid');
            if ($fbclid) {
                $data['_fbc'] = 'fb.1.' . time() . '.' . $fbclid;
            }
        }

        return $data;
    }

    public function buildFromBooking($booking): array
    {
        $user = null;
        if ($booking->user_id) {
            $user = User::find($booking->user_id);
        }

        return $this->buildEnhancedUserData($user, [
            'email' => $booking->customer_email ?? $user?->email,
            'phone' => $booking->customer_phone ?? $user?->phone,
            'name' => $booking->customer_name ?? $user?->name,
            'city' => $booking->city ?? null,
            'country' => $booking->country ?? 'PS',
        ]);
    }

    public function buildFromLead(array $leadData): array
    {
        return $this->buildEnhancedUserData(null, [
            'email' => $leadData['email'] ?? null,
            'phone' => $leadData['phone'] ?? null,
            'name' => $leadData['name'] ?? null,
            'city' => $leadData['city'] ?? null,
            'country' => $leadData['country'] ?? 'PS',
            'external_id' => $leadData['id'] ?? null,
        ]);
    }

    public function calculateMatchRate(array $userData): array
    {
        $fields = ['em', 'ph', 'fn', 'ln', 'ct', 'country', 'external_id', '_fbp', '_fbc'];
        $matched = 0;
        $details = [];

        foreach ($fields as $field) {
            $has = !empty($userData[$field]);
            $details[$field] = $has ? 'matched' : 'missing';
            if ($has) $matched++;
        }

        $rate = round(($matched / count($fields)) * 100, 1);

        $quality = match (true) {
            $rate >= 70 => 'excellent',
            $rate >= 50 => 'good',
            $rate >= 30 => 'fair',
            default => 'poor',
        };

        return [
            'rate' => $rate,
            'quality' => $quality,
            'matched_fields' => $matched,
            'total_fields' => count($fields),
            'details' => $details,
        ];
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '970')) return $phone;
        if (str_starts_with($phone, '0')) return '970' . substr($phone, 1);
        if (strlen($phone) === 9) return '970' . $phone;
        return $phone;
    }
}
