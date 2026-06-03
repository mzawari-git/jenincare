<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_id' => 'required|string|max:255',
            'platform' => 'sometimes|string|max:50',
            'device_model' => 'sometimes|string|max:255',
            'os_version' => 'sometimes|string|max:50',
            'app_version' => 'sometimes|string|max:50',
        ]);

        $device = Device::updateOrCreate(
            [
                'device_id' => $data['device_id'],
                'platform' => $data['platform'] ?? 'android',
            ],
            [
                'user_id' => $request->user()?->id,
                'device_model' => $data['device_model'] ?? null,
                'os_version' => $data['os_version'] ?? null,
                'app_version' => $data['app_version'] ?? null,
            ]
        );

        return response()->json(['success' => true, 'device_id' => $device->id]);
    }

    public function updateFcm(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|string',
            'fcm_token' => 'required|string',
        ]);

        Device::where('device_id', $request->device_id)
            ->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['success' => true]);
    }
}
