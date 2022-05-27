<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findByUserRole($id) : array
    {
        if ($this->getEntityManager()->find(User::class, $id)->hasRole("ROLE_ADMIN")) {
            return $this->findAll();
        } else {
            return $this->findBy(["owner" => $id]);
        }
    }

    public function getProjectOwners() : array
    {
        return $this->createQueryBuilder("p")
            ->select(["distinct u.email", "u.id"])
            ->innerJoin("p.owner", "u")
            ->getQuery()
            ->getResult();
    }
}