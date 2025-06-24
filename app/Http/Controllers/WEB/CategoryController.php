<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;


use App\Models\Category;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Illuminate\Support\Facades\Log as LaravelLog; // for system logs if needed


class CategoryController extends Controller
{

    // Get all categories
    public function index()
    {
        try {
            $categories = Category::all();
            return view('pages.categories.index', compact('categories'));
        } catch (Exception $e) {
            $this->logError('Failed to load categories: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading categories.');
        }
    }

    // Show form to create a new category
    public function create()
    {
        return view('pages.categories.create');
    }

    // Store new category
public function store(Request $request)
{
    $user = Auth::user();

    try {
        if ($user->role === 'admin') {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            Category::create([
                'name' => $request->name,
                'description' => $request->description,
                'user_id' => $user->id,
            ]);

            return redirect()->route('categories.index')->with('success', 'Category created successfully.');
        } else {
            return back()->with('error', 'You do not have permission to create a category.');
        }
    } catch (Exception $e) {
        \Log::error('Failed to create category: ' . $e->getMessage() . ' in ' . $e->getFile() . ' at line ' . $e->getLine());
        return back()->withInput()->with('error', 'Failed to create category: ' . $e->getMessage());
    }
}

    // Show form to edit an existing category
    public function edit(Category $category)
    {
        try {
            return view('pages.categories.edit', compact('category'));
        } catch (Exception $e) {
            $this->logError('Failed to load category for editing: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong while loading the category.');
        }
    }

    // Update existing category
    public function update(Request $request, Category $category)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $category->update($request->only('name', 'description'));

            $this->logSuccess('Updated category: ' . $category->name);
            return redirect()->route('categories.index')->with('success', 'Category created successfully.');
        } catch (Exception $e) {
            $this->logError('Failed to update category: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update category.');
        }
    }

    // Delete category
    public function destroy(Category $category)
    {
        try {
            $name = $category->name;
            $category->delete();

            $this->logSuccess("Deleted category: $name");
            return redirect()->route('categories.index')->with('success', 'Category created successfully.');
        } catch (Exception $e) {
            $this->logError('Failed to delete category: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete category.');
        }
    }

    private function logSuccess($description)
    {
        $this->log('success', $description);
    }

    private function logError($description)
    {
        $this->log('error', $description);
    }

    private function log($type, $description)
    {
        Log::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'description' => $description,
        ]);
    }
}
