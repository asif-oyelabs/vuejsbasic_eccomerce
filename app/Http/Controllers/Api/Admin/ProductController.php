<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * List products with search, filters and pagination.
     * GET /api/admin/products
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->boolean('status'));
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate(min(max((int) $request->input('per_page', 10), 1), 100));

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
     * Create a product.
     * POST /api/admin/products
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', 'unique:products,slug'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'original_price' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'qty' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $storedFiles = [];

        try {
            $product = DB::transaction(function () use (
                $request,
                $validated,
                &$storedFiles
            ) {
                $mainImage = null;

                if ($request->hasFile('main_image')) {
                    $mainImage = $request->file('main_image')
                        ->store('products/main', 'public');

                    $storedFiles[] = $mainImage;
                }

                $slug = $this->makeUniqueSlug(
                    $validated['slug'] ?? $validated['name']
                );

                $product = Product::create([
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'category_id' => $validated['category_id'],
                    'description' => $validated['description'] ?? null,
                    'main_image' => $mainImage,
                    'original_price' => $validated['original_price'],
                    'price' => $validated['price'],
                    'qty' => $validated['qty'],
                    'status' => $validated['status'],
                ]);

                foreach ($request->file('images', []) as $image) {
                    $path = $image->store('products/gallery', 'public');
                    $storedFiles[] = $path;

                    $product->images()->create([
                        'image' => $path,
                    ]);
                }

                return $product;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedFiles);
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data' => $this->formatProduct(
                $product->load(['category', 'images'])
            ),
        ], 201);
    }

    /**
     * Show one product.
     * GET /api/admin/products/{product}
     */
    public function show(Product $product)
    {
        $product->load(['category', 'images']);

        return response()->json([
            'success' => true,
            'message' => 'Product fetched successfully.',
            'data' => $this->formatProduct($product),
        ]);
    }

    /**
     * Update a product and optionally add/remove images.
     * POST /api/admin/products/{product}
     * Use multipart/form-data with _method=PUT for file uploads.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable',
                'string',
                'max:220',
                Rule::unique('products', 'slug')->ignore($product->id),
            ],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_main_image' => ['nullable', 'boolean'],
            'original_price' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'qty' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => [
                'integer',
                Rule::exists('product_images', 'id')
                    ->where('product_id', $product->id),
            ],
        ]);

        $storedFiles = [];
        $filesToDelete = [];

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                $product,
                &$storedFiles,
                &$filesToDelete
            ) {
                $mainImage = $product->main_image;

                if ($request->hasFile('main_image')) {
                    $newMainImage = $request->file('main_image')
                        ->store('products/main', 'public');

                    $storedFiles[] = $newMainImage;

                    if ($mainImage) {
                        $filesToDelete[] = $mainImage;
                    }

                    $mainImage = $newMainImage;
                } elseif ($request->boolean('remove_main_image')) {
                    if ($mainImage) {
                        $filesToDelete[] = $mainImage;
                    }

                    $mainImage = null;
                }

                $slug = $this->makeUniqueSlug(
                    $validated['slug'] ?? $validated['name'],
                    $product->id
                );

                $product->update([
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'category_id' => $validated['category_id'],
                    'description' => $validated['description'] ?? null,
                    'main_image' => $mainImage,
                    'original_price' => $validated['original_price'],
                    'price' => $validated['price'],
                    'qty' => $validated['qty'],
                    'status' => $validated['status'],
                ]);

                if (!empty($validated['remove_image_ids'])) {
                    $imagesToRemove = $product->images()
                        ->whereIn('id', $validated['remove_image_ids'])
                        ->get();

                    foreach ($imagesToRemove as $image) {
                        $filesToDelete[] = $image->image;
                        $image->delete();
                    }
                }

                foreach ($request->file('images', []) as $image) {
                    $path = $image->store('products/gallery', 'public');
                    $storedFiles[] = $path;

                    $product->images()->create([
                        'image' => $path,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedFiles);
            throw $e;
        }

        // Delete replaced/removed files only after the database update succeeds.
        Storage::disk('public')->delete(array_filter($filesToDelete));

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => $this->formatProduct(
                $product->fresh()->load(['category', 'images'])
            ),
        ]);
    }

    /**
     * Delete a product and its images.
     * DELETE /api/admin/products/{product}
     */
    public function destroy(Product $product)
    {
        $files = collect([$product->main_image])
            ->merge($product->images()->pluck('image'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($product) {
            // Gallery records are also deleted by the FK cascade.
            $product->delete();
        });

        Storage::disk('public')->delete($files);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Generate a unique slug.
     */
    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = \Illuminate\Support\Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'product';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Format product response with image URLs.
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
            'images' => $product->images->map(fn ($image) => [
                'id' => $image->id,
                'image' => $image->image,
                'image_url' => Storage::disk('public')->url($image->image),
            ])->values(),
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];
    }
}
