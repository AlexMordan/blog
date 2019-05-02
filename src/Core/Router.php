<?php


namespace App\Core;


use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\Middleware\AuthMiddleware;
use App\Controllers\Middleware\GuestMiddleware;
use App\Controllers\PageController;
use App\Controllers\PostController;
use App\Controllers\TagController;
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
                    $app->get('[/{page:[0-9]+}]', CategoryController::class.':index');
                    $app->delete('/delete', CategoryController::class.':remove');
                    $app->map(['GET', 'POST'], '/add', CategoryController::class.':add');
                    $app->get('/edit/{id:[0-9]+}', CategoryController::class.':getEdit');
                    $app->post('/edit', CategoryController::class.':postEdit');
                });

                $app->group('/posts', function () use ($app){
                    $app->get('[/{page:[0-9]+}]', PostController::class.':index');
                    $app->get('/view/{id:[0-9]+}', PostController::class.':view');
                    $app->get( '/edit/{id:[0-9]+}' ,PostController::class.':getEdit');
                    $app->post( '/edit' ,PostController::class.':postEdit');
                    $app->delete('/delete', PostController::class.':delete');
                    $app->map(['GET', 'POST'], '/add' ,PostController::class.':add');
                    $app->map(['GET', 'POST'], '/upload-image', PostController::class.':uploadImage');
                });

                $app->group('/tags', function () use ($app){
                    $app->get('[/{page:[0-9]+}]', TagController::class.':index');
                    $app->map(['GET', 'POST'], '/ajax-add' ,TagController::class.':add');
                    $app->map(['GET','POST'],'/ajax-edit[/{id:[0-9]+}]', TagController::class.':edit');
                    $app->delete('/delete', TagController::class.':delete');
                });
            })->add(new AuthMiddleware($container));

        $app->get('/about', PageController::class.':about');
        $app->map(['GET', 'POST'], '/contacts', PageController::class.':contacts');
        $app->get('/category/{id:[0-9]+}', PageController::class.':category');
        $app->get('/tag/{slug}', PageController::class.':tag');
        $app->get('/{slug}', PageController::class.':read');



    }
}