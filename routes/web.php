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

$router->get('/key', function () {
    return \Illuminate\Support\Str::random(32);
});

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('/login', 'AuthController@login');
    $router->post('/register', 'AuthController@register');
    $router->get('/users', ['middleware' => 'auth', 'uses' => 'AuthController@user']);
});

$router->group(['middleware' => 'auth'], function ($router) {
    // * put all endpoint that need authentication here
    $router->get('/todos', 'TodoController@index');
    $router->get('/todos/{id}', 'TodoController@show');
    $router->post('/todos', 'TodoController@store');
    $router->put('/todos/{id}', 'TodoController@update');
    $router->delete('/todos/{id}', 'TodoController@delete');
});
