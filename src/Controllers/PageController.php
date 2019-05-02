<?php


namespace App\Controllers;

use App\Models\Repositories\CategoryRepositoryInterface;
use App\Models\Repositories\PostRepositoryInterface;
use App\Models\Repositories\TagRepositoryInterface;
use App\Models\Tag;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

class PageController extends BaseController
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryRepository = $this->container->get(CategoryRepositoryInterface::class);
        $this->postRepository = $this->container->get(PostRepositoryInterface::class);
        $this->tagRepository = $this->container->get(TagRepositoryInterface::class);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function home(Request $request, Response $response)
    {


        $posts = $this->postRepository->findLastPublished(8);
        $sliderPosts = $this->postRepository->getRandomPublished(3);
        return $this->view->render(
            $response,
            'pages/home.twig',
            array_merge([
                'posts' => $posts,
                'slider_posts' => $sliderPosts
            ], $this->getSidebarViewModel())
        );
    }

    public function read(Request $request, Response $response)
    {
        $slug = $request->getAttribute('slug');
        if(empty($slug)){
            throw new NotFoundException($request, $response);
        }
        $post = $this->postRepository->findOneBySlug($slug);
        if(empty($post) || !$post->isPublished()){
            throw new NotFoundException($request, $response);
        }
        return $this->view->render(
            $response,
            'pages/read.twig',
            array_merge(['post' => $post], $this->getSidebarViewModel())
        );

    }

    public function category(Request $request, Response $response)
    {
        $categoryId = $request->getAttribute('id');
        if(empty($categoryId)){
            throw new NotFoundException($request, $response);
        }
        $category = $this->categoryRepository->findOneById($categoryId);
        if(empty($category)){
            throw new NotFoundException($request, $response);
        }
        $posts = $this->postRepository->findPublishedByCategoryId($categoryId, 10, 0);
        return $this->view->render(
            $response,
            'pages/view.twig',
            array_merge($this->getSidebarViewModel(),
                [
                    'category' => $category,
                    'posts' => $posts
                ])
        );
    }

    public function tag(Request $request, Response $response)
    {

        $slug = $request->getAttribute('slug');
        if(empty($slug)){
            throw new NotFoundException($request, $response);
        }
        $tag = $this->tagRepository->findBySlug($slug);
        if(empty($tag)){
            throw new NotFoundException($request, $response);
        }
        $posts = $this->postRepository->findPublishedByTagId($tag->getId(), 10, 0);
        return $this->view->render(
            $response,
            'pages/view.twig',
            array_merge($this->getSidebarViewModel(),
                [
                    'tag' => $tag,
                    'posts' => $posts
                ])
        );
    }

    private function getSidebarViewModel()
    {
        $categories = $this->categoryRepository->findAll();
        //TODO: change used only
        $tags = $this->tagRepository->findAll();
        $tags = Tag::getRandomTags($tags, 10);
        $randomPosts = $this->postRepository->getRandomPublished(3);
        return [
            'categories' => $categories,
            'randomPosts' => $randomPosts,
            'tags' => $tags
        ];
    }

    public function contacts(Request $request, Response $response)
    {
        if($request->isGet()){
            return $this->view->render(
                $response,
                'pages/contacts.twig',
                array_merge(['sent' => false], $this->getSidebarViewModel()));
        }
        $params = $request->getParsedBody();
        $name = $params['name'];
        $phone = $params['phone'];
        $email = $params['email'];
        $message = $params['message'];

        $log = new Logger('name');
        $log->pushHandler(new StreamHandler(getenv('ROOT').'/../logs/messages.log', Logger::INFO));
        $log->info("Name: $name, Email: $email, Phone: $phone, Message: $message");
        return $this->view->render(
            $response,
            'pages/contacts.twig',
            array_merge(['sent' => true], $this->getSidebarViewModel()));

    }

    public function about(Request $request, Response $response)
    {
        return $this->view->render($response, 'pages/about.twig', $this->getSidebarViewModel());
    }

}