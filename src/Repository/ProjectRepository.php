<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * @throws Exception
     */
    public function getProjectOwners() : array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT DISTINCT u.email, u.id 
            FROM project as p INNER JOIN user as u
            ON p.owner_id = u.id;
        ";
        $stmt = $conn->prepare($sql);
        return $stmt ->executeQuery() -> fetchAllAssociative();
    }
}