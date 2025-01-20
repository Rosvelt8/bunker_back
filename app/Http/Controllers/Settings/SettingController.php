<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Retrieve the first settings row
     */
    public function getSettings()
    {
        $settings = Settings::first();
        return response()->json($settings);
    }

    /**
     * Update the first settings row
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'rate_pay_order' => 'integer|min:0|max:100',
            'rate_pay_delivery' => 'integer|min:0|max:100',
            'order_saler_limit' => 'integer|min:0',
        ]);

        $settings = Settings::first();
        if ($settings) {
            $settings->update($request->all());
            return response()->json([
                'message' => 'Settings updated successfully',
                'settings' => $settings,
            ]);
        }

        return response()->json(['message' => 'Settings not found'], 404);
    }
}
