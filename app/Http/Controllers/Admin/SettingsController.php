<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private $settingKeys = [
        'video_call_cost',
        'live_cost',
        'vip_plan_cost',
    ];

    public function getSettings()
    {
        try {
            $settings = [];
            foreach ($this->settingKeys as $key) {
                $settings[$key] = AppSetting::getValue($key, '0');
            }

            return response()->json(['success' => true, 'data' => $settings]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'video_call_cost' => 'nullable|numeric|min:0',
                'live_cost'       => 'nullable|numeric|min:0',
                'vip_plan_cost'   => 'nullable|numeric|min:0',
            ]);

            foreach ($validated as $key => $value) {
                if ($value !== null) {
                    AppSetting::setValue($key, (string) $value);
                }
            }

            $settings = [];
            foreach ($this->settingKeys as $key) {
                $settings[$key] = AppSetting::getValue($key, '0');
            }

            return response()->json(['success' => true, 'message' => 'Settings updated successfully', 'data' => $settings]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => ['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
}
