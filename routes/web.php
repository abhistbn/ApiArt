<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// API Routes untuk Articles
$router->group(['prefix' => 'api'], function () use ($router) {
    
    // CRUD Articles
    $router->get('/articles', 'ArticleController@index');           // List all articles
    $router->get('/articles/{id}', 'ArticleController@show');       // Get single article
    $router->post('/articles', 'ArticleController@store');          // Create new article
    $router->put('/articles/{id}', 'ArticleController@update');     // Update article (BAGIAN ANDA)
    $router->delete('/articles/{id}', 'ArticleController@destroy'); // Delete article
    
    // Additional routes
    $router->get('/articles/category/{category}', 'ArticleController@getByCategory'); // Filter by category
});

// ========================================
// RUTE UNTUK KONSUMSI PUBLIK (PublicArt)
// ========================================
// Endpoint ini tidak memerlukan otentikasi dan hanya menampilkan data yang dipublikasikan.
$router->group(['prefix' => 'public'], function () use ($router) {
    // Mendapatkan semua artikel yang dipublikasikan
    // Akan memanggil method `indexPublic` di ArticleController
    $router->get('/articles', 'ArticleController@indexPublic');

    // Mendapatkan detail satu artikel yang dipublikasikan berdasarkan ID
    // Akan memanggil method `showPublic` di ArticleController
    $router->get('/articles/{id}', 'ArticleController@showPublic');

    // Mendapatkan daftar kategori yang aktif
    // Akan memanggil method `indexPublic` di CategoryController (controller baru)
    $router->get('/categories', 'CategoryController@indexPublic');
});