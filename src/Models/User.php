<?php


namespace App\Models;


use App\Models\Repositories\UserRepositoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class User
 * @package App\Models
 */
class User
{
    /**
     * @var integer
     */
    protected $id;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $password;
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $avatar;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    public function __construct(ContainerInterface $container)
    {
        $this->userRepository = $container->get(UserRepositoryInterface::class);
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
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * @param string|null $avatar
     */
    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    /**
     * Update User
     * @return bool
     */
    public function save():bool
    {
        return $this->userRepository->update($this);
    }

}