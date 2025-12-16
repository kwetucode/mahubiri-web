<?php

namespace App\Http\Controllers\Api\Sermon;

use App\Http\Controllers\Controller;
use App\Models\CategorySermon;
use App\Http\Resources\CategorySermonResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategorySermonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $categories = CategorySermon::withCount(['sermons' => function ($q) {
            $q->where('is_published', true);
        }])->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => CategorySermonResource::collection($categories),
            'message' => 'Liste des catégories récupérée avec succès.'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:category_sermons,name',
        ]);
        $category = CategorySermon::create($validated);
        return response()->json([
            'success' => true,
            'data' => new CategorySermonResource($category),
            'message' => 'Catégorie créée avec succès.'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CategorySermon $categorySermon): JsonResponse
    {
        $categorySermon->loadCount(['sermons' => function ($q) {
            $q->where('is_published', true);
        }]);
        return response()->json([
            'success' => true,
            'data' => new CategorySermonResource($categorySermon),
            'message' => 'Catégorie récupérée avec succès.'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CategorySermon $categorySermon): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:category_sermons,name,' . $categorySermon->id,
        ]);
        $categorySermon->update($validated);
        return response()->json([
            'success' => true,
            'data' => new CategorySermonResource($categorySermon),
            'message' => 'Catégorie mise à jour avec succès.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CategorySermon $categorySermon): JsonResponse
    {
        $categorySermon->delete();
        return response()->json([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès.'
        ]);
    }
}
