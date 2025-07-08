<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CheckUpdate;
use Illuminate\Http\Request;

class CheckUpdateController extends Controller
{
    public function get()
    {
        $update = CheckUpdate::getInstance();
        return response()->json([
            'message' => 'Data Retrieved Successfully',
            'data' => $update
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'ios_version' => 'nullable|integer|min:0|max:255',
            'android_version' => 'nullable|integer|min:0|max:255',
            'ios' => 'nullable|boolean',
            'android' => 'nullable|boolean',
            'android_link' => 'nullable|string|max:255',
            'ios_link' => 'nullable|string|max:255',
        ]);

        $update = CheckUpdate::getInstance();
        $update->updateInstance($validated);

        return response()->json([
            'message' => 'Update configuration modified successfully',
            'data' => CheckUpdate::getInstance()
        ]);
    }
}