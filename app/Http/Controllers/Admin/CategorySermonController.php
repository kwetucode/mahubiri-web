<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategorySermon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategorySermonController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        $allowedSorts = ['name', 'created_at', 'sermons_count'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $categories = CategorySermon::query()
            ->withCount('sermons')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (CategorySermon $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'sermons_count' => $category->sermons_count,
                'created_at' => $category->created_at?->format('d/m/Y H:i'),
                'created_at_human' => $category->created_at?->diffForHumans(),
            ]);

        return Inertia::render('Admin/SermonCategories/Index', [
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:category_sermons,name'],
        ]);

        $category = CategorySermon::create($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
            'message' => 'Catégorie créée avec succès.',
        ], 201);
    }

    public function update(Request $request, CategorySermon $categorySermon): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category_sermons', 'name')->ignore($categorySermon->id),
            ],
        ]);

        $categorySermon->update($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $categorySermon->id,
                'name' => $categorySermon->name,
            ],
            'message' => 'Catégorie mise à jour avec succès.',
        ]);
    }

    public function destroy(CategorySermon $categorySermon): JsonResponse
    {
        if ($categorySermon->sermons()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette catégorie car elle contient des sermons.',
            ], 422);
        }

        $categorySermon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès.',
        ]);
    }
}
