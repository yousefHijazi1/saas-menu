<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request, Restaurant $restaurant)
    {
        $orders = $restaurant->orders()
            ->with(['items', 'table', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($orders);
    }

    public function store(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'table_id' => 'nullable',
            'customer_name' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.item_name' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.extras' => 'nullable|array',
            'items.*.note' => 'nullable|string',
        ]);

        $subtotal = collect($request->items)->sum(function ($item) {
            return $item['unit_price'] * $item['quantity'];
        });

        $tax = $subtotal * 0.1; // 10% tax
        $total = $subtotal + $tax;

        // If table_id is provided as table number (string), find the actual table
        $tableId = null;
        if ($request->table_id) {
            if (is_numeric($request->table_id)) {
                $tableId = $request->table_id;
            } else {
                $table = Table::where('restaurant_id', $restaurant->id)
                    ->where('table_number', $request->table_id)
                    ->first();
                $tableId = $table ? $table->id : null;
            }
        }

        $order = $restaurant->orders()->create([
            'table_id' => $tableId,
            'user_id' => auth()->id() ?? null,
            'customer_name' => $request->customer_name,
            'note' => $request->note,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            $order->items()->create([
                'menu_item_id' => $item['menu_item_id'],
                'item_name' => $item['item_name'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'extras' => $item['extras'] ?? null,
                'note' => $item['note'] ?? null,
                'total_price' => $item['unit_price'] * $item['quantity'],
            ]);
        }

        if ($request->table_id) {
            $table = Table::find($request->table_id);
            $table->update(['status' => 'occupied']);
        }

        $order->load(['items', 'table']);
        return response()->json($order, 201);
    }

    public function show(Order $order)
    {
        $order->load(['items', 'table', 'user']);
        return response()->json($order);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,served,paid,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        if ($request->status === 'paid' && $order->table_id) {
            $order->table->update(['status' => 'available']);
        }

        return response()->json($order->load('items'));
    }

    public function destroy(Order $order)
    {
        $order->items()->delete();
        $order->delete();
        return response()->json(['message' => 'Order deleted']);
    }
}
