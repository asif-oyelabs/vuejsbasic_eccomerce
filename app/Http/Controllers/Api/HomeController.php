<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * GET /api/home/categories
     * Return the latest 5 active categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', true)
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->image,
                    'image_url' => $category->image
                        ? Storage::disk('public')->url($category->image)
                        : null,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Homepage categories fetched successfully.',
            'data' => $categories,
        ]);
    }

    /**
     * GET /api/home/products
     * Return the latest 8 active products.
     */
    public function products(): JsonResponse
    {
        $products = Product::query()
            ->where('status', true)
            ->whereHas('category', function ($query) {
                $query->where('status', true);
            })
            ->with([
                'category:id,name,slug',
            ])
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'category_id' => $product->category_id,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ] : null,
                    'description' => $product->description,
                    'main_image' => $product->main_image,
                    'main_image_url' => $product->main_image
                        ? Storage::disk('public')->url($product->main_image)
                        : null,
                    'original_price' => $product->original_price,
                    'price' => $product->price,
                    'qty' => $product->qty,
                    'status' => $product->status,
                    'created_at' => $product->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Homepage products fetched successfully.',
            'data' => $products,
        ]);
    }
}
