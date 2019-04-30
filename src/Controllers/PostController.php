<?php


namespace App\Controllers;


use App\Models\Post;
use App\Models\Repositories\CategoryRepositoryInterface;
use App\Models\Repositories\PostRepositoryInterface;
use App\Models\Repositories\TagRepositoryInterface;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

class PostController extends BaseController
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /** @var int  */
    private const PER_PAGE_COUNT = 5;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->postRepository = $this->container->get(PostRepositoryInterface::class);
        $this->categoryRepository = $this->container->get(CategoryRepositoryInterface::class);
        $this->tagRepository = $this->container->get(TagRepositoryInterface::class);
    }

    public function index(Request $request, Response $response)
    {
        $page = $request->getAttribute('page');
        if(empty($page))
        {
            $page = 1;
        }
        $total = $this->postRepository->count();
        $pages = ceil($total/self::PER_PAGE_COUNT);
        $posts = $this->postRepository->findPage($page, self::PER_PAGE_COUNT);
        return $this->view->render($response,'posts/index.twig', ['posts' => $posts, 'page' => $page, 'pages' => $pages, 'total' => $total]);
    }

    public function view(Request $request, Response $response)
    {
        $id = (int) $request->getAttribute('id');
        $post = $this->postRepository->findOneById($id);
        if(empty($post))
        {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
        return $this->view->render($response, 'posts/view.twig', ['post' => $post]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws \Exception
     */
    public function add(Request $request, Response $response)
    {
        $categories = $this->postRepository->getCategoriesKeysPairs();
        $tags = $this->tagRepository->findAll();
        if($request->isGet())
        {
            return $this->view->render(
                $response,
                'posts/add.twig',
                ['categories' => $categories, 'tags' => $tags, 'validate' => false]);
        }


        $params = $request->getParsedBody();
        $post = new Post($this->container);
        if(!empty($params['inputTagsIds'])){
            $post->setTagsIds($params['inputTagsIds']);
        }

        $post->setSlug($params['inputSlug']);
        $post->setTitle($params['inputTitle']);
        $post->setDescription($params['inputDescription']);
        $post->setBody($params['inputBody']);
        $post->setCategoryId($params['inputCategoryId']);
        $post->setPublishedAt(\DateTime::createFromFormat('Y-m-d H:i',$params['inputPublishAt']));
        $post->setPublished($params['inputPublish']);
        $errors = [];
        if(! $this->postRepository->checkSlugAvailability($post->getSlug()))
        {
            $errors['slug'] = 'Slug already used';
        }
        $errors = array_merge($errors, Post::validate($post));
        if(!empty($errors))
        {
            return $this->view->render($response, 'posts/add.twig', [
                'categories' => $categories,
                'errors' => $errors,
                'post' => $post,
                'tags' => $tags,
                'validate' => true
            ]);

        }
        $uploadedFiles = $request->getUploadedFiles();
        if (!empty($uploadedFiles['inputImage'])) {
            $directory = getenv('ROOT') . '/public/' . getenv('UPLOAD_DIR');
            $uploadedFile = $uploadedFiles['inputImage'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                $post->setImage('/' . getenv('UPLOAD_DIR') . '/' . $filename);
            }
        }
        $post->setAuthorId($this->auth->user()->getId());
        $newPost = $this->postRepository->create($post);
        $this->flash->addMessage(self::MESSAGE_INFO, 'Post added');
        return $response->withRedirect('/admin/posts/view/'.$newPost->getId());
    }


    /**
     * @param Request $request
     * @param Response $response
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Slim\Exception\NotFoundException
     */
    public function getEdit(Request $request, Response $response)
    {
        $id = (int) $request->getAttribute('id');
        $post = $this->postRepository->findOneById($id);
        if(empty($post))
        {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
        $categories = $this->postRepository->getCategoriesKeysPairs();
        return $this->view->render($response, 'posts/edit.twig', [
            'categories' => $categories,
            'post' => $post,
            'validate' => false
        ]);
    }

    public function postEdit(Request $request, Response $response)
    {
        $params = $request->getParsedBody();
        $post = $this->postRepository->findOneById($params['id']);
        $post->setTitle($params['inputTitle']);
        $post->setDescription($params['inputDescription']);
        $post->setBody($params['inputBody']);
        $post->setCategoryId($params['inputCategoryId']);
        $post->setPublishedAt(\DateTime::createFromFormat('Y-m-d H:i',$params['inputPublishAt']));
        $post->setPublished($params['inputPublish']);
        $errors = [];
        if($post->getSlug()!=$params['inputSlug'])
        {
            if(!$this->postRepository->checkSlugAvailability($params['inputSlug']))
            {
                $errors['slug'] = 'Slug already used';
            }
            $post->setSlug($params['inputSlug']);
        }
        $errors = array_merge($errors, Post::validate($post));
        if(!empty($errors))
        {
            $categories = $this->postRepository->getCategoriesKeysPairs();
            return $this->view->render($response, 'posts/edit.twig', [
                'categories' => $categories,
                'errors' => $errors,
                'post' => $post,
                'validate' => true
            ]);
        }
        $uploadedFiles = $request->getUploadedFiles();
        if (!empty($uploadedFiles['inputImage'])) {
            $directory = getenv('ROOT') . '/public/' . getenv('UPLOAD_DIR');
            $uploadedFile = $uploadedFiles['inputImage'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                $post->setImage('/' . getenv('UPLOAD_DIR') . '/' . $filename);
            }
        }

        $this->postRepository->update($post);
        $this->flash->addMessage(self::MESSAGE_INFO, 'Post updated');
        return $response->withRedirect('/admin/posts/view/'.$post->getId());
    }

    public function delete(Request $request, Response $response)
    {
        $id = (int) $request->getParsedBody()['id'];
        $this->postRepository->deleteOneById($id);
        $this->flash->addMessage(self::MESSAGE_WARNING, 'Post successfully deleted');
        return $response->withRedirect('/admin/posts');
    }
    public function uploadImage(Request $request, Response $response)
    {

        /** * @var UploadedFile[] $uploadedFiles */
        try {
            $uploadedFiles = $request->getUploadedFiles();
            if (!empty($uploadedFiles['upload'])) {
                $directory = getenv('ROOT') . '/public/' . getenv('UPLOAD_DIR');
                $uploadedFile = $uploadedFiles['upload'];
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $filename = $this->moveUploadedFile($directory, $uploadedFile);
                    $url = '/' . getenv('UPLOAD_DIR') . '/' . $filename;
                    return  $response->withJson(['uploaded' => true, 'url' => $url]);
                }
            }
        }catch (\Exception $exception) {
            return $response->withJson([
                'uploaded' => false,
                'error' => ['message' => 'could not upload this image']
            ]);
        }
        return  $response->withJson(['uploaded' => false, 'error' => ['message' => 'bad image']]);

    }
}