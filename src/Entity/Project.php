<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=5)
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 5,
     *      max = 5
     * )
     * @var
     */
    protected $token; //5 latin symbols

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @var
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="user")
     * @var User
     */
    protected $owner;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set task Author
     * @param User|null $author
     * @return void
     */
    public function setOwner(UserInterface $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * Return task author
     * @return User|null
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
