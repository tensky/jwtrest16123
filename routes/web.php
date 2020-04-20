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



$router->post('/registrasi', 'Controller@registrasi');
$router->put('/update', 'Controller@updateData');
$router->get('/get', 'Controller@getUser');

//Social Media
$router->get('/socmed', 'Controller@readSocialMedia');
$router->post('/socmed', 'Controller@createSocialMedia');
$router->put('/socmed/{id}', 'Controller@updateSocialMedia');
$router->delete('/socmed/{id}', 'Controller@deleteSocialMedia');
