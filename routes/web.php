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
