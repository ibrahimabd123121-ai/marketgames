<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * عرض التصنيفات النشطة.
     */
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * عرض تصنيف واحد.
     */
    public function show(Category $category): JsonResponse
    {
        abort_unless($category->status, 404);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * إنشاء تصنيف جديد.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'image' => $request->hasFile('image')
                ? ['required', 'image', 'max:5120']
                : ['sometimes', 'nullable', 'string', 'max:255'],
            'remove_image' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'boolean'],
        ]);

        $validated['slug'] = $validated['slug']
            ?? Str::slug($validated['name']);
        $validated['image'] = $request->hasFile('image')
            ? $this->storeImage($request)
            : ($validated['image'] ?? null);
        unset($validated['remove_image']);

        if (Category::where('slug', $validated['slug'])->exists()) {
            if ($request->hasFile('image')) {
                $this->deleteStoredImage($validated['image']);
            }

            return response()->json([
                'success' => false,
                'message' => 'هذا الرابط المختصر مستخدم بالفعل. يرجى تحديد slug مختلف.',
            ], 422);
        }

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء التصنيف بنجاح.',
            'data' => $category,
        ], 201);
    }

    /**
     * تعديل تصنيف.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:categories,id',
                Rule::notIn([$category->id]),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:180',
                Rule::unique('categories', 'slug')->ignore($category->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'image' => $request->hasFile('image')
                ? ['required', 'image', 'max:5120']
                : ['sometimes', 'nullable', 'string', 'max:255'],
            'remove_image' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'boolean'],
        ]);

        $oldImage = $category->image;
        unset($validated['remove_image']);

        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request);
        } elseif ($request->boolean('remove_image')) {
            $validated['image'] = null;
        }

        $category->update($validated);

        if ($oldImage !== $category->image) {
            $this->deleteStoredImage($oldImage);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل التصنيف بنجاح.',
            'data' => $category->fresh(),
        ]);
    }

    /**
     * حذف تصنيف.
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف تصنيف يحتوي على تصنيفات فرعية.',
            ], 422);
        }

        if ($category->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف تصنيف مرتبط بمنتجات.',
            ], 422);
        }

        $image = $category->image;
        $category->delete();
        $this->deleteStoredImage($image);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التصنيف بنجاح.',
        ]);
    }

    private function storeImage(Request $request): string
    {
        $path = $request->file('image')->store('categories', 'public');

        abort_unless(is_string($path), 500, 'Unable to store the uploaded image.');

        return $path;
    }

    private function deleteStoredImage(?string $image): void
    {
        if (! $image || ! str_starts_with($image, 'categories/')) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($image) && ! $disk->delete($image)) {
            Log::warning('Unable to delete category image.', ['path' => $image]);
        }
    }
}
