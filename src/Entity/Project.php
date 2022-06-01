<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
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
    private $id;

    /**
     * @ORM\Column(type="string", length=6)
     * @var
     */
    private $keyProject;

    /**
     * @ORM\Column(type="string", length=255)
     * @var
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="user")
     * @var User
     */
    private $owner;

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(UserInterface $owner = null)
    {
        $this->owner = $owner;
    }

    public function getId()
    {
        return $this -> id;
    }

    public function getKeyproject(): ?string
    {
        return $this->keyProject;
    }

    public function setKeyProject(string $keyProject = null): self
    {
        $this->keyProject = $this->createKey();
        return $this;
    }

    private function createKey(): string
    {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz-_', 1)), 1, 5);
    }

    public function getName()
    {
        return $this -> name;
    }

    public function setName($name): void
    {
        $this -> name = $name;
    }
}
