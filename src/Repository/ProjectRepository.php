<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findAllByUser(User $user): array
    {
        $allProjects = $this->findAll();
        if(in_array('ROLE_ADMIN', $user->getRoles())) {
            return $allProjects;
        }

        return $this->filterByUser($allProjects, $user);
    }

    private function filterByUser(array $projects, User $user): array{
        $result = array();
        foreach ($projects as $project ){
            if ($project->getOwner()->getEmail() === $user->getEmail()){
                array_push($result, $project);
            }
        }
        return $result;
//        return $project->getOwner()->getEmail() === $user->getEmail();
    }
}