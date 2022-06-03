<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @var
     */
    protected $name;

    /**
     * @ORM\Column(type="text", length=255)
     * @Assert\NotBlank
     * @var
     */
    protected $description;

    /**
     * @Assert\NotBlank
     * @Assert\Type("\DateTime")
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    protected $dueDate;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 0})
     * @var bool
     */
    protected $isCompleted = false;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="user")
     * @var User
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="project")
     * @var Project
     */
    protected $project;

    /**
     * Create empty task
     */
    public function __construct()
    {
        $this->dueDate = new \DateTime('now');
        $this->isCompleted = false;
    }


    /**
     * Set task Author
     * @param User|null $author
     * @return void
     */
    public function setAuthor(UserInterface $author = null)
    {
        $this->author = $author;
    }


    /**
     * Return task author
     * @return User|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return Project|null
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject(Project $project = null)
    {
        $this->project = $project;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param mixed $dueDate
     */
    public function setDueDate($dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    /**
     * Return true if task is completed
     * @return bool
     */
    public function isCompleted() : bool
    {
        return $this->isCompleted;
    }

    /**
     * Set task to complete state
     * @param bool $isCompleted
     * @return void
     */
    public function setIsCompleted(bool $isCompleted = false)
    {
        $this->isCompleted = $isCompleted;
    }



}