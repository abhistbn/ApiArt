<?php

// ========================================
// 4. CONTROLLER: app/Http/Controllers/ArticleController.php
// ========================================

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
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

    // ===================================================
    // METODE UNTUK KONSUMSI PUBLIK (PublicArt)
    // ===================================================

    /**
     * GET ALL PUBLISHED ARTICLES for PublicArt frontend.
     * Endpoint: GET /public/articles
     */
    public function indexPublic(Request $request): JsonResponse
    {
        try {
            $query = Article::query()->published(); // Hanya ambil artikel yang statusnya 'published'

            // Implementasi pencarian berdasarkan query string 'search'
            if ($request->has('search')) {
                $searchTerm = $request->input('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('summary', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('content', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            // Implementasi filter kategori berdasarkan query string 'category_id'
            if ($request->has('category_id')) {
                $categoryId = $request->input('category_id');
                // Pastikan untuk mengabaikan filter jika category_id adalah 'all' dari frontend
                if ($categoryId !== 'all') {
                    $query->where('category_id', $categoryId);
                }
            }

            // Urutkan artikel berdasarkan published_at terbaru
            $articles = $query->orderBy('published_at', 'desc')->get();

            // Transformasi data untuk menyertakan nama kategori
            $transformedArticles = $articles->map(function($article) {
                // Mencari nama kategori berdasarkan category_id
                $category = Category::find($article->category_id);
                
                return [
                    'id' => $article->id,
                    'title' => $article->title,
                    'summary' => $article->summary,
                    'content' => $article->content, // Termasuk content untuk modal detail
                    'author' => $article->author,
                    'category_id' => $article->category_id,
                    'category_name' => $category ? $category->name : 'Uncategorized', // Tambahkan nama kategori
                    'created_at' => $article->created_at->toISOString(),
                    'updated_at' => $article->updated_at->toISOString(),
                    'published_at' => $article->published_at ? $article->published_at->toISOString() : null,
                    'featured_image' => $article->featured_image,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Articles fetched successfully for public view',
                'data' => $transformedArticles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch articles for public view',
                'error_code' => 'INTERNAL_SERVER_ERROR',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * GET SINGLE PUBLISHED ARTICLE for PublicArt frontend modal.
     * Endpoint: GET /public/articles/{id}
     */
    public function showPublic($id): JsonResponse
    {
        try {
            // Hanya ambil artikel jika statusnya 'published'
            $article = Article::published()->find($id);
            
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artikel tidak ditemukan atau belum diterbitkan'
                ], 404);
            }

            // Ambil nama kategori
            $category = Category::find($article->category_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'summary' => $article->summary,
                    'content' => $article->content, // Content penuh untuk modal
                    'author' => $article->author,
                    'category_id' => $article->category_id,
                    'category_name' => $category ? $category->name : 'Uncategorized',
                    'tags' => $article->tags,
                    'featured_image' => $article->featured_image,
                    'published_at' => $article->published_at ? $article->published_at->toISOString() : null,
                    'created_at' => $article->created_at->toISOString(),
                    'updated_at' => $article->updated_at->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil detail artikel publik',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
