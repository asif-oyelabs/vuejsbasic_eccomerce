<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{

    public function index(Request $request)
    {
        $cartItems = CartItem::where('user_id', $request->user()->id)
            ->with([
                'product:id,name,slug,main_image,price,qty,status,category_id',
                'product.category:id,name',
            ])
            ->latest()
            ->get();

        $items = $cartItems->map(function ($item) {
            $product = $item->product;

            if (!$product) {
                return null;
            }

            $price = (float) $product->price;
            $quantity = (int) $item->quantity;

            $image = $product->main_image
                ? asset('storage/' . ltrim($product->main_image, '/'))
                : null;

            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $image,
                'price' => $price,
                'quantity' => $quantity,
                'stock' => (int) $product->qty,
                'line_total' => round($price * $quantity, 2),
                'category' => $product->category?->name,
            ];
        })->filter()->values();

        $subtotal = round($items->sum('line_total'), 2);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'item_count' => $items->sum('quantity'),
                'subtotal' => $subtotal,
                'shipping_amount' => 0,
                'total_amount' => $subtotal,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $userId = $request->user()->id;

        $result = DB::transaction(function () use ($validated, $userId) {
            $product = Product::query()
                ->where('status', true)
                ->whereHas('category', function ($query) {
                    $query->where('status', true);
                })
                ->whereKey($validated['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw ValidationException::withMessages([
                    'product_id' => 'This product is unavailable.',
                ]);
            }

            $cartItem = CartItem::query()
                ->where('user_id', $userId)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $existingQuantity = $cartItem?->quantity ?? 0;
            $newQuantity = $existingQuantity + $validated['quantity'];

            if ($product->qty < $newQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->qty} unit(s) are available. "
                        . "You already have {$existingQuantity} in your cart.",
                ]);
            }

            if ($cartItem) {
                $cartItem->quantity = $newQuantity;
                $cartItem->price = $product->price;
                $cartItem->save();
            } else {
                $cartItem = CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'price' => $product->price,
                ]);
            }

            return $cartItem->load('product:id,name,price,main_image');
        });

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully.',
            'data' => [
                'id' => $result->id,
                'product_id' => $result->product_id,
                'quantity' => $result->quantity,
                'price' => $result->price,
                'product' => $result->product,
            ],
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem = CartItem::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $product = Product::whereKey($cartItem->product_id)
            ->where('status', true)
            ->firstOrFail();

        if ($validated['quantity'] > $product->qty) {
            return response()->json([
                'success' => false,
                'message' => "Only {$product->qty} unit(s) are available.",
            ], 422);
        }

        $cartItem->update([
            'quantity' => $validated['quantity'],
            'price' => $product->price,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart quantity updated successfully.',
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $cartItem = CartItem::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $cartItem->delete();

        return response()->json([
            'success' => true,
                'message' => 'Product removed from your cart.',
            ]);
        }

}
