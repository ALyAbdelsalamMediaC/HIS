<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Policy;

class PolicyController extends Controller
{
      public function index()
    {
        try {
            $policies = Policy::with([
                'addedBy:id,name,email',
                'category:id,title' // Load category with specific fields
            ])
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Policies retrieved successfully',
                'data' => $policies
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve policies',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific policy by ID with category and addedBy data
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $policy = Policy::with([
                'addedBy:id,name,email',
                'category:id,title'
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Policy retrieved successfully',
                'data' => $policy
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Policy not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
