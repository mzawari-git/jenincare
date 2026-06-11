<?php

namespace App\Services\Audience;

use App\Models\MarketingSetting;
use App\Models\Meta\MetaAdAccount;
use App\Services\Meta\FacebookGraphService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AudienceUploadService
{
    private FacebookGraphService $graph;

    public function __construct(FacebookGraphService $graph)
    {
        $this->graph = $graph;
    }

    public function uploadCsvToCustomAudience(int $audienceId, string $csvPath): array
    {
        $audience = \App\Models\CustomAudience::findOrFail($audienceId);

        if ($audience->platform !== 'meta') {
            return ['success' => false, 'message' => 'رفع CSV مدعوم فقط لـ Meta audiences'];
        }

        $rows = $this->parseCsv($csvPath);
        if (empty($rows)) {
            return ['success' => false, 'message' => 'الملف فارغ أو غير صالح'];
        }

        $account = MetaAdAccount::where('is_active', true)->first();
        if (!$account) {
            return ['success' => false, 'message' => 'لا يوجد حساب Meta متصل'];
        }

        $this->graph->setUserAccessToken($account->access_token);

        $schema = [];
        $lineNumber = 0;
        $uploaded = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $lineNumber++;
            $userData = $this->mapRowToUserData($row);

            if (empty($userData)) {
                $failed++;
                continue;
            }

            $schema[] = $userData;
            $uploaded++;

            if (count($schema) >= 1000) {
                $this->batchUpload($audience->platform_audience_id, $schema, $account->ad_account_id);
                $schema = [];
                usleep(500000);
            }
        }

        if (!empty($schema)) {
            $this->batchUpload($audience->platform_audience_id, $schema, $account->ad_account_id);
        }

        $audience->update([
            'audience_size' => $audience->audience_size + $uploaded,
            'last_synced_at' => now(),
        ]);

        return [
            'success' => true,
            'uploaded' => $uploaded,
            'failed' => $failed,
            'total_rows' => $lineNumber,
            'message' => "تم رفع {$uploaded} من أصل {$lineNumber} صف",
        ];
    }

    public function uploadPhoneNumbersToCustomAudience(int $audienceId, array $phones): array
    {
        $audience = \App\Models\CustomAudience::findOrFail($audienceId);

        $account = MetaAdAccount::where('is_active', true)->first();
        if (!$account) {
            return ['success' => false, 'message' => 'لا يوجد حساب Meta متصل'];
        }

        $this->graph->setUserAccessToken($account->access_token);

        $schema = [];
        foreach ($phones as $phone) {
            $normalized = $this->normalizePhone($phone);
            if ($normalized) {
                $schema[] = ['phone_number' => hash('sha256', $normalized)];
            }
        }

        if (empty($schema)) {
            return ['success' => false, 'message' => 'لا توجد أرقام صالحة'];
        }

        $result = $this->batchUpload($audience->platform_audience_id, $schema, $account->ad_account_id);

        $audience->update([
            'audience_size' => $audience->audience_size + count($schema),
            'last_synced_at' => now(),
        ]);

        return $result + ['uploaded' => count($schema)];
    }

    public function uploadEmailsToCustomAudience(int $audienceId, array $emails): array
    {
        $audience = \App\Models\CustomAudience::findOrFail($audienceId);

        $account = MetaAdAccount::where('is_active', true)->first();
        if (!$account) {
            return ['success' => false, 'message' => 'لا يوجد حساب Meta متصل'];
        }

        $this->graph->setUserAccessToken($account->access_token);

        $schema = [];
        foreach ($emails as $email) {
            $email = strtolower(trim($email));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $schema[] = ['email' => hash('sha256', $email)];
            }
        }

        if (empty($schema)) {
            return ['success' => false, 'message' => 'لا توجد بريد إلكتروني صالح'];
        }

        $result = $this->batchUpload($audience->platform_audience_id, $schema, $account->ad_account_id);

        $audience->update([
            'audience_size' => $audience->audience_size + count($schema),
            'last_synced_at' => now(),
        ]);

        return $result + ['uploaded' => count($schema)];
    }

    private function batchUpload(?string $audienceFbId, array $schema, string $adAccountId): array
    {
        if (!$audienceFbId) {
            return ['success' => false, 'message' => 'Audience ID غير موجود'];
        }

        try {
            $result = $this->graph->post("{$audienceFbId}/users", [
                'payload' => [
                    'schema' => array_keys((array)reset($schema)),
                    'data' => array_values($schema),
                ],
            ]);

            return ['success' => true, 'batch_id' => $result['batch_id'] ?? null];
        } catch (\Exception $e) {
            Log::error('Audience upload failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطأ في الرفع: ' . $e->getMessage()];
        }
    }

    private function parseCsv(string $path): array
    {
        $rows = [];
        if (!file_exists($path)) return $rows;

        $handle = fopen($path, 'r');
        if (!$handle) return $rows;

        $headers = fgetcsv($handle);
        if (!$headers) return $rows;

        $headers = array_map('strtolower', array_map('trim', $headers));

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $rows[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $rows;
    }

    private function mapRowToUserData(array $row): array
    {
        $data = [];

        if (!empty($row['email'])) {
            $data['email'] = hash('sha256', strtolower(trim($row['email'])));
        }
        if (!empty($row['phone'])) {
            $data['phone_number'] = hash('sha256', $this->normalizePhone($row['phone']));
        }
        if (!empty($row['fn']) || !empty($row['first_name']) || !empty($row['firstname'])) {
            $name = $row['fn'] ?? $row['first_name'] ?? $row['firstname'] ?? '';
            $data['fn'] = hash('sha256', strtolower(trim($name)));
        }
        if (!empty($row['ln']) || !empty($row['last_name']) || !empty($row['lastname'])) {
            $name = $row['ln'] ?? $row['last_name'] ?? $row['lastname'] ?? '';
            $data['ln'] = hash('sha256', strtolower(trim($name)));
        }
        if (!empty($row['ct']) || !empty($row['city'])) {
            $city = $row['ct'] ?? $row['city'] ?? '';
            $data['ct'] = hash('sha256', strtolower(trim($city)));
        }
        if (!empty($row['country']) || !empty($row['co'])) {
            $country = $row['country'] ?? $row['co'] ?? '';
            $data['country'] = hash('sha256', strtolower(trim($country)));
        }
        if (!empty($row['zip']) || !empty($row['postalcode'])) {
            $zip = $row['zip'] ?? $row['postalcode'] ?? '';
            $data['zp'] = hash('sha256', trim($zip));
        }
        if (!empty($row['external_id']) || !empty($row['id'])) {
            $id = $row['external_id'] ?? $row['id'] ?? '';
            $data['external_id'] = hash('sha256', (string)$id);
        }

        return $data;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '970')) return $phone;
        if (str_starts_with($phone, '0')) return '970' . substr($phone, 1);
        if (strlen($phone) === 9) return '970' . $phone;
        return $phone;
    }

    public function getCsvTemplate(): string
    {
        return "email,phone,first_name,last_name,city,country,external_id\nexample@email.com,0591234567,John,Doe,Jenin,PS,cust_123\n";
    }
}
