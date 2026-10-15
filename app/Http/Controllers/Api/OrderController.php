<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Confirm the authenticated user's cart and create an order.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $order = DB::transaction(function () use ($user) {
            $cartItems = CartItem::where('user_id', $user->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Your shopping cart is empty.'],
                ]);
            }

            $preparedItems = [];
            $subtotal = 0;

            // Validate every product before creating the order.
            foreach ($cartItems as $cartItem) {
                $product = Product::whereKey($cartItem->product_id)
                    ->lockForUpdate()
                    ->first();

                if (!$product || !$product->status) {
                    throw ValidationException::withMessages([
                        'cart' => [
                            'A product in your cart is no longer available.',
                        ],
                    ]);
                }

                $quantity = (int) $cartItem->quantity;

                if ($quantity < 1 || $product->qty < $quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => [
                            "Only {$product->qty} unit(s) of {$product->name} are available.",
                        ],
                    ]);
                }

                // Use the current database price, not the client/cart total.
                $priceInPaise = (int) round((float) $product->price * 100);
                $lineTotalInPaise = $priceInPaise * $quantity;

                $subtotal += $lineTotalInPaise;

                $preparedItems[] = [
                    'cart_item_id' => $cartItem->id,
                    'product' => $product,
                    'quantity' => $quantity,
                    'price_in_paise' => $priceInPaise,
                    'total_in_paise' => $lineTotalInPaise,
                ];
            }

            // Shipping is currently free; update this when shipping is implemented.
            $shippingInPaise = 0;

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . Str::upper(Str::random(12)),
                'subtotal' => $subtotal / 100,
                'shipping_amount' => $shippingInPaise / 100,
                'total_amount' => ($subtotal + $shippingInPaise) / 100,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'ordered_at' => now(),
            ]);

            foreach ($preparedItems as $item) {
                $product = $item['product'];
                $quantity = $item['quantity'];

                $image = $product->main_image
                    ? asset('storage/' . ltrim($product->main_image, '/'))
                    : null;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $image,
                    'price' => $item['price_in_paise'] / 100,
                    'quantity' => $quantity,
                    'total' => $item['total_in_paise'] / 100,
                ]);

                // Reserve/decrement stock for this newly created order.
                $product->decrement('qty', $quantity);
            }

            CartItem::whereIn(
                'id',
                collect($preparedItems)->pluck('cart_item_id')
            )->delete();

            return $order->load('items');
        }, 3);

        return response()->json([
            'success' => true,
            'message' => 'Your order has been placed successfully.',
            'data' => $order,
        ], 201);
    }

    /**
     * List orders belonging to the authenticated user.
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Show one order belonging to the authenticated user.
     */
    public function show(Request $request, int $id)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }
}
