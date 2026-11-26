<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index()
    {
        $restaurants = Restaurant::all();
        return response()->json($restaurants);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'currency' => 'nullable|string|max:10',
        ]);

        $restaurant = Restaurant::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(4),
            'description' => $request->description,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'currency' => $request->currency ?? '$',
        ]);

        return response()->json($restaurant, 201);
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['categories', 'tables']);
        return response()->json($restaurant);
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'currency' => 'nullable|string|max:10',
            'is_active' => 'sometimes|boolean',
        ]);

        $restaurant->update($request->only([
            'name', 'description', 'address', 'phone', 'email', 'currency', 'is_active',
        ]));

        return response()->json($restaurant);
    }

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return response()->json(['message' => 'Restaurant deleted']);
    }

    public function menu(Restaurant $restaurant)
    {
        $categories = $restaurant->categories()
            ->with(['menuItems' => function ($query) {
                $query->where('is_available', true)->orderBy('sort_order');
            }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'restaurant' => $restaurant,
            'categories' => $categories,
        ]);
    }
}
