<?php

namespace App\Http\Controllers\Api\Sermon;

use App\Exceptions\ApiExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategorySermonResource;
use App\Models\CategorySermon;
use Illuminate\Http\Request;

class CetegorySermonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = (int) request()->query('per_page', 15);
        $search = request()->query('q');
        $query = CategorySermon::withCount('sermons')->orderBy('name', 'asc');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $categories = $query->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => CategorySermonResource::collection($categories),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:category_sermons,name',
        ]);
        try {
            CategorySermon::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
            ], 201);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'de la création de la catégorie', [
                'request_data' => $request->all()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = $this->chekExistCategory($id);
        return response()->json([
            'success' => true,
            'data' => new CategorySermonResource($category),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:category_sermons,name',
        ]);
        try {
            $category = $category = $this->chekExistCategory($id);
            $category->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
            ], 201);
        } catch (\Exception $e) {
            return ApiExceptionHandler::auto($e, 'de la création de la catégorie', [
                'request_data' => $request->all()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = $this->chekExistCategory($id);
        if ($category->sermons()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated sermons',
            ], 400);
        }
        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    //Make get sermon reusable
    protected function chekExistCategory($id)
    {
        $category = CategorySermon::with('sermons')->find($id);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }
        return $category;
    }
}
