<?php

// ========================================
// 4. CONTROLLER: app/Http/Controllers/ArticleController.php
// ========================================

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ArticleController extends Controller
{
    /**
     * UPDATE ARTICLE - Bagian Anda
     * PUT/PATCH /api/articles/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            // Cari artikel berdasarkan ID
            $article = Article::find($id);
            
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artikel tidak ditemukan',
                    'error_code' => 'ARTICLE_NOT_FOUND'
                ], 404);
            }

            // Validasi input
            $validatedData = $this->validate($request, [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'excerpt' => 'nullable|string|max:500',
                'author' => 'required|string|max:100',
                'category' => 'required|string|max:50',
                'tags' => 'nullable|string',
                'status' => 'required|in:draft,published,archived',
                'featured_image' => 'nullable|url',
                'published_at' => 'nullable|date'
            ]);

            // Jika status berubah ke published dan belum ada published_at, set sekarang
            if ($validatedData['status'] === 'published' && !$article->published_at) {
                $validatedData['published_at'] = Carbon::now();
            }

            // Jika status berubah dari published ke draft, hapus published_at
            if ($validatedData['status'] === 'draft' && $article->status === 'published') {
                $validatedData['published_at'] = null;
            }

            // Update artikel
            $article->update($validatedData);

            // Reload artikel dengan data terbaru
            $article->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Artikel berhasil diupdate',
                'data' => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'excerpt' => $article->excerpt,
                    'author' => $article->author,
                    'category' => $article->category,
                    'tags' => $article->tags,
                    'tags_array' => $article->tags_array,
                    'status' => $article->status,
                    'featured_image' => $article->featured_image,
                    'published_at' => $article->published_at ? $article->published_at->toISOString() : null,
                    'formatted_published_at' => $article->formatted_published_at,
                    'created_at' => $article->created_at->toISOString(),
                    'updated_at' => $article->updated_at->toISOString(),
                ]
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
                'message' => 'Terjadi kesalahan server',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * GET SINGLE ARTICLE - Untuk load data di form edit
     * GET /api/articles/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $article = Article::find($id);
            
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artikel tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'content' => $article->content,
                    'excerpt' => $article->excerpt,
                    'author' => $article->author,
                    'category' => $article->category,
                    'tags' => $article->tags,
                    'tags_array' => $article->tags_array,
                    'status' => $article->status,
                    'featured_image' => $article->featured_image,
                    'published_at' => $article->published_at ? $article->published_at->format('Y-m-d') : null,
                    'formatted_published_at' => $article->formatted_published_at,
                    'created_at' => $article->created_at->toISOString(),
                    'updated_at' => $article->updated_at->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
{
    try {
        $validatedData = $this->validate($request, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'author' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'tags' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'featured_image' => 'nullable|url',
            'published_at' => 'nullable|date'
        ]);

        if ($validatedData['status'] === 'published' && !isset($validatedData['published_at'])) {
            $validatedData['published_at'] = Carbon::now();
        }

        $article = Article::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil dibuat',
            'data' => $article
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
            'message' => 'Terjadi kesalahan server',
            'error_code' => 'INTERNAL_SERVER_ERROR',
            'debug' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

}
