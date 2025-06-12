<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use App\Models\Category;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
    /**
     * Display a listing of the policies.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $categories = Category::with([
            'policies' => function ($query) {
                $query->with('addedBy:id,name,username')->orderBy('id');
            }
        ])->get();

        return view('pages.policies.index', compact('categories'));
    }

    /**
     * Show the form for creating a new policy.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::all();

        return view('pages.policies.create', compact('categories'));
    }

    /**
     * Store a newly created policy in storage.
     *
     * @param HttpRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(HttpRequest $request)
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

            return redirect()->route('policies.index')
                ->with('success', 'Policy created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create policy: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified policy.
     *
     * @param Policy $policy
     * @return \Illuminate\View\View
     */
    public function show(Policy $policy)
    {
        return view('pages.policies.show', compact('policy'));
    }

    /**
     * Show the form for editing the specified policy.
     *
     * @param Policy $policy
     * @return \Illuminate\View\View
     */
    public function edit(Policy $policy)
    {
        $categories = Category::all();
        if (empty($policy->body)) {
            // Log::info('Policy body is empty for policy ID: ' . $policy->id);
        }
        return view('pages.policies.edit', compact('policy', 'categories'));
    }

    /**
     * Update the specified policy in storage.
     *
     * @param HttpRequest $request
     * @param Policy $policy
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(HttpRequest $request, Policy $policy)
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

            return redirect()->route('policies.index')
                ->with('success', 'Policy updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update policy: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified policy from storage.
     *
     * @param Policy $policy
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Policy $policy)
    {
        try {
            DB::beginTransaction();

            $policy->delete();

            DB::commit();

            return redirect()->route('policies.index')
                ->with('success', 'Policy deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to delete policy: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created policy category in storage.
     *
     * @param HttpRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCategory(HttpRequest $request)
    {
        $validatedData = $request->validate([
            'category_title' => 'required|string|max:255|unique:policy_categories,title',
        ]);

        try {
            Category::create([
                'title' => $validatedData['category_title'],
            ]);

            return redirect()->back()->with('success', 'Category added successfully!');
        } catch (\Exception $e) {
            // \Log::error('Category creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to add category: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified policy category.
     *
     * @param Category $category
     * @return \Illuminate\View\View
     */
    public function showCategory(Category $category)
    {
        return view('pages.policies.show_category', compact('category'));
    }

    /**
     * Update the specified policy category in storage.
     *
     * @param HttpRequest $request
     * @param Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCategory(HttpRequest $request, Category $category)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255|unique:policy_categories,title,' . $category->id,
        ]);

        try {
            DB::beginTransaction();

            $category->update($validatedData);

            DB::commit();

            return redirect()->back()
                ->with('success', 'Category updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update category: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified policy category from storage.
     *
     * @param Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCategory(Category $category)
    {
        try {
            DB::beginTransaction();

            if ($category->policies()->exists()) {
                throw new \Exception('Cannot delete category because it is associated with one or more policies.');
            }

            $category->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Category deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['category' => 'Failed to delete category: ' . $e->getMessage()])
                ->with('error', 'Failed to delete category: ' . $e->getMessage());
        }
    }
}
