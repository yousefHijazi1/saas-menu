<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(Restaurant $restaurant)
    {
        $items = $restaurant->menuItems()
            ->with('category')
            ->orderBy('sort_order')
            ->get();
        return response()->json($items);
    }

    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
            'ingredients' => 'nullable|array',
            'extras' => 'nullable|array',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $item = $restaurant->menuItems()->create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'image' => $request->image,
            'ingredients' => $request->ingredients,
            'extras' => $request->extras,
            'is_featured' => $request->is_featured ?? false,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json($item->load('category'), 201);
    }

    public function show(MenuItem $menuItem)
    {
        $menuItem->load('category');
        return response()->json($menuItem);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string',
            'ingredients' => 'nullable|array',
            'extras' => 'nullable|array',
            'is_available' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $menuItem->update($request->only([
            'category_id', 'name', 'description', 'price', 'discount_price',
            'image', 'ingredients', 'extras', 'is_available', 'is_featured', 'sort_order',
        ]));

        return response()->json($menuItem->load('category'));
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();
        return response()->json(['message' => 'Menu item deleted']);
    }
}
