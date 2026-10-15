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
}
