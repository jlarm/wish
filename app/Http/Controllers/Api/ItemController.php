<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()->items()->latest()->get();

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'link' => ['nullable', 'url'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'store' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'url'],
        ]);

        $item = $request->user()->items()->create($validated);

        return response()->json($item, 201);
    }

    public function show(Request $request, Item $item): JsonResponse
    {
        if ($item->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($item);
    }

    public function update(Request $request, Item $item): JsonResponse
    {
        if ($item->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'link' => ['nullable', 'url'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'store' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'url'],
            'purchased' => ['sometimes', 'boolean'],
            'delivered' => ['sometimes', 'boolean'],
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    public function destroy(Request $request, Item $item): JsonResponse
    {
        if ($item->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted successfully']);
    }
}
