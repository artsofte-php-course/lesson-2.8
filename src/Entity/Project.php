<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\ProjectRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use function Sodium\add;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project
{
    private static $generatedKeys = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $generatedKey;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="user")
     */
    private $owner;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGeneratedKey(): ?string
    {
        return $this->generatedKey;
    }

    public function setGeneratedKey(): void
    {
        $isUnique = true;
        $newKey = strval(random_int(100000, 999999));
        foreach (self::$generatedKeys as $key)
        {
            if ($key === $newKey)
            {
                $isUnique = false;
                break;
            }
        }
        if ($isUnique)
        {
            $this->generatedKey = $newKey;
            self::$generatedKeys[] = $newKey;
        }
        else
        {
            $this->setGeneratedKey();
        }
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner(UserInterface $owner): void
    {
        $this->owner = $owner;
    }
}