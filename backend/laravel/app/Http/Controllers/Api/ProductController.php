<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Product::with('category')->latest()->paginate(12),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'image' => $request->hasFile('image')
                ? ['required', 'image', 'max:5120']
                : ['sometimes', 'nullable', 'string', 'max:255'],
            'remove_image' => ['sometimes', 'boolean'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['image'] = $request->hasFile('image')
            ? $this->storeImage($request, 'products')
            : ($validated['image'] ?? null);
        unset($validated['remove_image']);

        if (Product::where('slug', $validated['slug'])->exists()) {
            if ($request->hasFile('image')) {
                $this->deleteStoredImage($validated['image'], 'products');
            }

            return response()->json([
                'message' => 'The generated slug already exists. Please provide a unique slug.',
            ], 422);
        }

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product->load('category'),
        ], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'unique:products,slug,'.$product->id,
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'image' => $request->hasFile('image')
                ? ['required', 'image', 'max:5120']
                : ['sometimes', 'nullable', 'string', 'max:255'],
            'remove_image' => ['sometimes', 'boolean'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'status' => ['sometimes', 'boolean'],
        ]);

        $oldImage = $product->image;
        unset($validated['remove_image']);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request, 'products');
        } elseif ($request->boolean('remove_image')) {
            $validated['image'] = null;
        }

        $product->update($validated);

        if ($oldImage !== $product->image) {
            $this->deleteStoredImage($oldImage, 'products');
        }

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product->fresh()->load('category'),
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->load('category'),
        ]);
    }

    public function delete(Product $product): JsonResponse
    {
        $image = $product->image;
        $product->delete();
        $this->deleteStoredImage($image, 'products');

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }

    private function storeImage(Request $request, string $directory): string
    {
        $path = $request->file('image')->store($directory, 'public');

        abort_unless(is_string($path), 500, 'Unable to store the uploaded image.');

        return $path;
    }

    private function deleteStoredImage(?string $image, string $directory): void
    {
        if (! $image || ! str_starts_with($image, $directory.'/')) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($image) && ! $disk->delete($image)) {
            Log::warning('Unable to delete product image.', ['path' => $image]);
        }
    }
}
