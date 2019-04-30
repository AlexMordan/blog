<?php


namespace App\Models\Repositories;


use App\Models\Post;
use PDO;
use Psr\Container\ContainerInterface;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    /**
     * PostRepository constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }


    /**
     * Find one Post by Id
     * @param int $id
     * @return Post|null
     */
    public function findOneById(int $id): ?Post
    {
        $stmt = $this->pdo->prepare('SELECT * FROM posts WHERE id=:id LIMIT 1');
        $stmt->setFetchMode(PDO::FETCH_CLASS, Post::class, [$this->container]);
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        return $post ? $post : null;
    }

    /**
     * Find all Posts
     * @return Post[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM posts');
        $stmt->setFetchMode(PDO::FETCH_CLASS, Post::class, [$this->container]);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Delete one Post by Id
     * @param int $id
     * @return bool
     */
    public function deleteOneById(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id=:id');
        $stmt->execute(array('id' => $id));
        return (bool)$stmt->rowCount();
    }

    /**
     * Save new Post
     * @param Post $post
     * @return Post
     */
    public function create(Post $post): Post
    {
        $stmt = $this->pdo->prepare('INSERT INTO posts (`title`, `slug`, `image`, `description`, `body`, `created_at`,
                   `updated_at`, `published_at`, `published`, `category_id`, `author_id`) VALUES (
                     :title, :slug, :image, :description, :body, :created_at,
                   :updated_at, :published_at, :published, :category_id, :author_id)');
        $stmt->execute([
            'title' => $post->getTitle(),
            'slug' => $post->getSlug(),
            'image' => $post->getImage(),
            'description' => $post->getDescription(),
            'body' => $post->getBody(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'published_at' => $post->getPublishedAt()->format("Y-m-d H:i:s"),
            'published' => (int)$post->isPublished(),
            'category_id' => $post->getCategoryId(),
            'author_id' => $post->getAuthorId()
        ]);
        $tagsIds = $post->getTagsIds();
        $id = (integer)$this->pdo->lastInsertId();
        if (!empty($tagsIds)) {
            $this->assignTagsToPost($id, $tagsIds);
        }
        $post = $this->findOneById($id);
        return $post;
    }

    /**
     * Update Post
     * @param Post $post
     * @return bool
     */
    public function update(Post $post): bool
    {
        $stmt = $this->pdo->prepare('UPDATE posts SET
                title=:title, slug=:slug, image=:image, description=:description, body=:body, created_at=:created_at,
                updated_at=:updated_at, published_at=:published_at, published=:published, category_id=:category_id
                WHERE id=:id');
        $stmt->execute([
            'title' => $post->getTitle(),
            'slug' => $post->getSlug(),
            'image' => $post->getImage(),
            'description' => $post->getDescription(),
            'body' => $post->getBody(),
            'created_at' => $post->getCreatedAt()->format("Y-m-d H:i:s"),
            'updated_at' => date('Y-m-d H:i:s'),
            'published_at' => $post->getPublishedAt()->format("Y-m-d H:i:s"),
            'published' => $post->isPublished(),
            'category_id' => $post->getCategoryId(),
            'id' => $post->getId()
        ]);
        return (bool)$stmt->rowCount();
    }

    /**
     * Find current page
     * @param int $page
     * @param int $count
     * @return Post[]
     */
    public function findPage(int $page, int $count): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM posts ORDER BY updated_at DESC LIMIT :limit OFFSET :offset');
        $stmt->setFetchMode(PDO::FETCH_CLASS, Post::class, [$this->container]);
        $stmt->execute([
            'limit' => $count,
            'offset' => (($page-1)*$count)
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Count post in DB
     * @return int
     */
    public function count(): int
    {
        return $this->pdo->query('SELECT count(*) FROM posts')->fetchColumn();
    }

    /**
     * Check is slug available
     * @param string $slug
     * @return bool
     */
    public function checkSlugAvailability(string $slug): bool
    {
        $stmt = $this->pdo->prepare('SELECT * FROM posts WHERE slug=:slug');
        $stmt->execute(['slug' => $slug]);
        return !(bool)$stmt->rowCount();
    }

    /** Get category array ['id' => 'name']
     * @return array
     */
    public function getCategoriesKeysPairs(): array
    {
        $stmt = $this->pdo->query('SELECT `id`, `name` FROM categories');
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Assign tags to array (many to many)
     * @param int $postId
     * @param array $tagsIds
     */
    public function assignTagsToPost(int $postId, array $tagsIds): void
    {
        $this->pdo->beginTransaction();
        foreach ($tagsIds as $tagId)
        {
            $stmt = $this->pdo->prepare('INSERT INTO `posts_tags` (`post_id`, `tag_id`) 
                                       VALUES (:post_id, :tag_id)');
            $stmt->execute([
                'post_id' => $postId,
                'tag_id' => $tagId
            ]);
        }
        $this->pdo->commit();
    }
}