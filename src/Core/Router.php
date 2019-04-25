<?php


namespace App\Core;


use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\Middleware\AuthMiddleware;
use App\Controllers\Middleware\GuestMiddleware;
use App\Controllers\PageController;
use App\Controllers\PostController;
use App\Controllers\UserController;
use Slim\App;

class Router
{
    /**
     * @param App $app
     */
    public static function setRoutes(&$app)
    {
        $container = $app->getContainer();
        $app->get('/', PageController::class.':home')
            ->setName('home');

        $app->map(['GET', 'POST'], '/signin', AuthController::class.':signIn')
            ->setName('signIn')
            ->add(new GuestMiddleware($container));

        $app->map(['GET', 'POST'], '/signup', AuthController::class.':signUp')
            ->add(new GuestMiddleware($container));

        $app->get('/signOut', AuthController::class.':signOut')
            ->add(new AuthMiddleware($container));

        $app->map(['GET', 'POST'], '/profile', UserController::class.':profile')
            ->add(new AuthMiddleware($container));

        $app->group('/admin', function () use ($app) {
                $app->group('/categories', function () use ($app){
                    $app->get('[/{page:[0-9]+}]', CategoryController::class.':adminIndex');
                    $app->delete('/delete', CategoryController::class.':adminRemove');
                    $app->map(['GET', 'POST'], '/add', CategoryController::class.':adminAdd');
                    $app->get('/edit/{id:[0-9]+}', CategoryController::class.':getEdit');
                    $app->post('/edit', CategoryController::class.':postEdit');
                });

                $app->group('/posts', function () use ($app){
                    $app->get('[/{page:[0-9]+}]', PostController::class.':adminIndex');
                    $app->get('/view/{id:[0-9]+}', PostController::class.':adminView');
                    $app->get( '/edit/{id:[0-9]+}' ,PostController::class.':getEdit');
                    $app->post( '/edit' ,PostController::class.':postEdit');
                    $app->delete('/delete', PostController::class.':delete');
                    $app->map(['GET', 'POST'], '/add' ,PostController::class.':add');
                    $app->map(['GET', 'POST'], '/upload-image', PostController::class.':uploadImage');
                });
            })->add(new AuthMiddleware($container));

    }
}