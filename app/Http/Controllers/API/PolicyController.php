<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\PolicyCategoryResource;
use App\Http\Resources\PolicyResource;
use App\Models\Policy;
use App\Models\PolicyCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
     public function index()
    {
        $categories = PolicyCategory::with([
            'policies' => function ($query) {
                $query->with('addedBy:id,name,email')->orderBy('id');
            }
        ])->get();

        return PolicyCategoryResource::collection($categories);
    }

    public function create()
    {
        $categories = PolicyCategory::all();
        return response()->json([
            'categories' => PolicyCategoryResource::collection($categories)
        ], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category_id' => 'required|exists:policy_categories,id',
        ]);

        try {
            DB::beginTransaction();

            $policy = Policy::create(array_merge($validatedData, [
                'added_by' => Auth::id(),
            ]));

            DB::commit();

            return new PolicyResource($policy);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to create policy: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Policy $policy)
    {
        return new PolicyResource($policy);
    }

    public function edit(Policy $policy)
    {
        $categories = PolicyCategory::all();
        return response()->json([
            'policy' => new PolicyResource($policy),
            'categories' => PolicyCategoryResource::collection($categories)
        ], 200);
    }

    public function update(Request $request, Policy $policy)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'category_id' => 'required|exists:policy_categories,id',
        ]);

        try {
            DB::beginTransaction();

            $policy->update($validatedData);

            DB::commit();

            return new PolicyResource($policy);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to update policy: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Policy $policy)
    {
        try {
            DB::beginTransaction();

            $policy->delete();

            DB::commit();

            return response()->json(['message' => 'Policy deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to delete policy: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeCategory(Request $request)
    {
        $validatedData = $request->validate([
            'category_title' => 'required|string|max:255|unique:policy_categories,title',
        ]);

        try {
            $category = PolicyCategory::create([
                'title' => $validatedData['category_title'],
            ]);

            return new PolicyCategoryResource($category);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to add category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showCategory(PolicyCategory $category)
    {
        return new PolicyCategoryResource($category);
    }

    public function updateCategory(Request $request, PolicyCategory $category)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255|unique:policy_categories,title,' . $category->id,
        ]);

        try {
            DB::beginTransaction();

            $category->update($validatedData);

            DB::commit();

            return new PolicyCategoryResource($category);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyCategory(PolicyCategory $category)
    {
        try {
            DB::beginTransaction();

            if ($category->policies()->exists()) {
                throw new \Exception('Cannot delete category because it is associated with one or more policies.');
            }

            $category->delete();

            DB::commit();

            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }
}
