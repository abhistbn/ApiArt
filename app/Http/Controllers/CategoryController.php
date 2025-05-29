<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str; // Untuk slug, pastikan diinstal jika belum: composer require illuminate/support

class CategoryController extends Controller
{
    // ===================================================
    // METODE UNTUK KONSUMSI PUBLIK (PublicArt)
    // ===================================================

    /**
     * GET ALL ACTIVE CATEGORIES for PublicArt frontend.
     * Endpoint: GET /public/categories
     */
    public function indexPublic(): JsonResponse
    {
        try {
            // Hanya ambil kategori yang statusnya aktif (is_active = true)
            $categories = Category::where('is_active', true)
                                ->orderBy('name', 'asc')
                                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Active categories fetched successfully for public view',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories for public view',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // ===================================================
    // METODE UNTUK ADMIN (CRUD - contoh, bisa disesuaikan)
    // ===================================================

    /**
     * Display a listing of all categories (including inactive ones) for admin.
     * Endpoint: GET /api/categories
     */
    public function index(): JsonResponse
    {
        try {
            $categories = Category::orderBy('name', 'asc')->get();
            return response()->json([
                'success' => true,
                'message' => 'All categories fetched successfully (for admin)',
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories (admin)',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created category in storage.
     * Endpoint: POST /api/categories
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $this->validate($request, [
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string',
                'color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/', // Validasi format hex color
                'is_active' => 'boolean',
            ]);

            $validatedData['slug'] = Str::slug($validatedData['name']); // Generate slug

            $category = Category::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified category.
     * Endpoint: GET /api/categories/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching category',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified category in storage.
     * Endpoint: PUT/PATCH /api/categories/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $validatedData = $this->validate($request, [
                'name' => 'required|string|max:255|unique:categories,name,' . $id, // Unique except for itself
                'description' => 'nullable|string',
                'color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
                'is_active' => 'boolean',
            ]);

            $validatedData['slug'] = Str::slug($validatedData['name']); // Update slug

            $category->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     * Endpoint: DELETE /api/categories/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}