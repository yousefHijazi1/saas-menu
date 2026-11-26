<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WaiterCall;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class WaiterCallController extends Controller
{
    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'table_number' => 'required|string|max:50',
            'note' => 'nullable|string|max:500',
        ]);

        $call = $restaurant->waiterCalls()->create([
            'table_number' => $request->table_number,
            'note' => $request->note,
        ]);

        return response()->json($call, 201);
    }

    public function index(Restaurant $restaurant)
    {
        $calls = $restaurant->waiterCalls()
            ->where('is_handled', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($calls);
    }

    public function markHandled(WaiterCall $waiterCall)
    {
        $waiterCall->update(['is_handled' => true]);
        return response()->json(['message' => 'Call marked as handled']);
    }
}
