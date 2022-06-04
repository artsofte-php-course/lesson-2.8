<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column (type="integer")
     */
    protected $id;

    /**
     * @ORM\Column (type="string", length=255, unique=true)
     * @Assert\NotBlank
     * @var
     */
    protected $projectKey;

    /**
     * @ORM\Column (type="string", length=255)
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
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="project", orphanRemoval=true)
     */
    protected $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * set project Owner
     * @param User | null $owner
     * @return void
     */
    public function setOwner(UserInterface $owner = null)
    {
        $this -> owner = $owner;
    }

    /**
     * return project owner
     * @return User | null
     */
    public function getOwner(): ?User
    {
        return $this -> owner;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this -> name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this -> id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this -> name = $name;
    }

    /**
     * @return mixed
     */
    public function getProjectKey()
    {
        return $this -> projectKey;
    }

    /**
     * @param mixed $projectKey
     */
    public function setProjectKey($projectKey): void
    {
        $this -> projectKey = $projectKey;
    }
}