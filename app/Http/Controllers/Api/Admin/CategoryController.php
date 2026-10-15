<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * List categories with search and pagination.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->input('per_page', 10), 1), 100);

        $categories = Category::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->boolean('status'));
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage);

        $categories->getCollection()->transform(
            fn (Category $category) => $this->formatCategory($category)
        );

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data' => $categories,
        ]);
    }

    /**
     * Create a category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:categories,slug'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = $this->makeUniqueSlug(
            $validated['slug'] ?? $validated['name']
        );

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')
                ->store('categories', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['status'] = $validated['status'] ?? true;

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => $this->formatCategory($category),
        ], 201);
    }

    /**
     * Show one category.
     */
    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatCategory($category),
        ]);
    }

    /**
     * Update a category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:180',
                Rule::unique('categories', 'slug')->ignore($category->id),
            ],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('name', $validated) && !array_key_exists('slug', $validated)) {
            $validated['slug'] = $this->makeUniqueSlug(
                $validated['name'],
                $category->id
            );
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $validated['image'] = $request->file('image')
                ->store('categories', 'public');
        } elseif (array_key_exists('image', $validated) && $validated['image'] === null) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $validated['image'] = null;
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data' => $this->formatCategory($category->fresh()),
        ]);
    }

    /**
     * Delete a category.
     */
    public function destroy(Category $category)
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }

    /**
     * Generate a unique slug.
     */
    private function makeUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Category::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Format category response.
     */
    private function formatCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'image' => $category->image,
            'image_url' => $category->image
                ? asset('storage/' . $category->image)
                : null,
            'sort_order' => $category->sort_order,
            'status' => $category->status,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ];
    }
}
