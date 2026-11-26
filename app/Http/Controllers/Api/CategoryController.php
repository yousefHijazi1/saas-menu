<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Restaurant $restaurant)
    {
        $categories = $restaurant->categories()->orderBy('sort_order')->get();
        return response()->json($categories);
    }

    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $category = $restaurant->categories()->create([
            'name' => $request->name,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        $category->load('menuItems');
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'icon' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($request->only(['name', 'icon', 'sort_order', 'is_active']));
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
