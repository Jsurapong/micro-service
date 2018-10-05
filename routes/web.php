<?php

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



$router->group([
    'prefix' => '/books',
    'namespace' => '\App\Http\Controllers'
], function () use ($router) {
    $router->get('/', 'BooksController@index');
    $router->get('/{id:[\d]+}', [
    'as' => 'books.show',
    'uses'=>'BooksController@show'
]);
    $router->post('/', 'BooksController@store');
    $router->put('/{id:[\d]+}', 'BooksController@update');
    $router->delete('/{id:[\d]+}', 'BooksController@destroy');
});



$router->group([
    'prefix' => '/authors',
    'namespace' => '\App\Http\Controllers'
], function () use ($router) {
    $router->get('/', 'AuthorsController@index');
    $router->post('/', 'AuthorsController@store');
    $router->get('/{id:[\d]+}', [
        'as' => 'authors.show',
        'uses' => 'AuthorsController@show'
    ]);
    $router->put('/{id:[\d]+}', 'AuthorsController@update');
    $router->delete('/{id:[\d]+}', 'AuthorsController@destroy');
});

$router->group([
    'prefix' => '/bundles',
    'namespace' => '\App\Http\Controllers'
], function () use ($router) {
    $router->get('/{id:[\d]+}', [
        'as' => 'bundles.show',
        'uses' => 'BundlesController@show'
    ]);
    $router->put(
        '/{bundleId:[\d]+}/books/{bookId:[\d]+}',
        'BundlesController@addBook'
    );
    $router->delete(
        '/{bundleId:[\d]+}/books/{bookId:[\d]+}',
        'BundlesController@removeBook'
    );
});
