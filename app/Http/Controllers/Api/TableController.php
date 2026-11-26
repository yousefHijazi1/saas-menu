<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index(Restaurant $restaurant)
    {
        $tables = $restaurant->tables()->orderBy('table_number')->get();
        return response()->json($tables);
    }

    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'table_number' => 'required|string|max:50',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $table = $restaurant->tables()->create([
            'table_number' => $request->table_number,
            'capacity' => $request->capacity ?? 4,
        ]);

        return response()->json($table, 201);
    }

    public function show(Table $table)
    {
        return response()->json($table);
    }

    public function update(Request $request, Table $table)
    {
        $request->validate([
            'table_number' => 'sometimes|string|max:50',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'sometimes|in:available,occupied,reserved',
        ]);

        $table->update($request->only(['table_number', 'capacity', 'status']));
        return response()->json($table);
    }

    public function destroy(Table $table)
    {
        $table->delete();
        return response()->json(['message' => 'Table deleted']);
    }
}
