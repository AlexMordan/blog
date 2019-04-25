<?php


namespace App\Models;


use App\Models\Repositories\CategoryRepositoryInterface;
use DateTime;
use Psr\Container\ContainerInterface;

/**
 * Class Post
 * @package App\Models
 */
class Post extends BaseModel
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $slug;
    /**
     * @var string|null
     */
    protected $image;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var string
     */
    protected $body;
    /**
     * @var DateTime
     */
    protected $created_at;
    /**
     * @var DateTime
     */
    protected $updated_at;
    /**
     * @var DateTime
     */
    protected $published_at;
    /**
     * @var bool
     */
    protected $published;
    /**
     * @var int
     */
    protected $category_id;

    /**
     * @var Category
     */
    protected $category;

    protected $dates = [
        'published_at',
        'updated_at',
        'created_at'
    ];
    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        foreach($this->dates as $date)
        {
            $property = $this->{$date};
            $this->{$date} = new DateTime($property);
        }
        $this->categoryRepository = $this->container->get(CategoryRepositoryInterface::class);
        if(!empty($this->category_id))
        {
            $this->category = $this->categoryRepository->findOneById($this->category_id);
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string|null $image
     */
    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    /**
     * @param DateTime $created_at
     */
    public function setCreatedAt(DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updated_at;
    }

    /**
     * @param DateTime $updated_at
     */
    public function setUpdatedAt(DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    /**
     * @return DateTime
     */
    public function getPublishedAt(): DateTime
    {
        return $this->published_at;
    }

    /**
     * @param DateTime $published_at
     */
    public function setPublishedAt(DateTime $published_at): void
    {
        $this->published_at = $published_at;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {

        return $this->published;
    }

    /**
     * @param bool $published
     */
    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->category_id;
    }

    /**
     * @param int $category_id
     */
    public function setCategoryId(int $category_id): void
    {
        $this->category_id = $category_id;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

}