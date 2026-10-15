<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * GET /api/products
     *
     * Supported filters:
     * search, category_id, min_price, max_price, page, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Product::query()
            ->where('status', true)
            ->whereHas('category', function ($categoryQuery) {
                $categoryQuery->where('status', true);
            })
            ->with(['category:id,name,slug']);

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (isset($validated['min_price']) && $validated['min_price'] !== '') {
            $query->where('price', '>=', $validated['min_price']);
        }

        if (isset($validated['max_price']) && $validated['max_price'] !== '') {
            $query->where('price', '<=', $validated['max_price']);
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate($validated['per_page'] ?? 12);

        $products->getCollection()->transform(
            fn (Product $product) => $this->formatProduct($product)
        );

        return response()->json([
            'success' => true,
            'message' => 'Products fetched successfully.',
            'data' => $products,
        ]);
    }

    /**
     * GET /api/product-categories
     * Active categories for the public product filter sidebar.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'success' => true,
            'message' => 'Categories fetched successfully.',
            'data' => $categories,
        ]);
    }

    /**
     * Format public product response.
     */
    private function formatProduct(Product $product): array
    {
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
            'status' => (bool) $product->status,
            'created_at' => $product->created_at,
        ];
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->where('status', true)
            ->whereHas('category', function ($query) {
                $query->where('status', true);
            })
            ->with([
                'category:id,name,slug',
                'images:id,product_id,image',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $mainImage = $product->main_image
            ? Storage::disk('public')->url($product->main_image)
            : null;

        $gallery = collect([$mainImage])
            ->merge(
                $product->images->map(
                    fn ($image) => Storage::disk('public')->url($image->image)
                )
            )
            ->filter()
            ->unique()
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Product details fetched successfully.',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'category' => $product->category,
                'price' => $product->price,
                'original_price' => $product->original_price,
                'qty' => $product->qty,
                'main_image_url' => $mainImage,
                'gallery' => $gallery,
            ],
        ]);
    }
}
