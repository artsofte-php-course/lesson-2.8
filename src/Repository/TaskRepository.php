<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findAllByProject(Project $project): array
    {
        $allTasks = $this->findAll();
        return $this->filterByProject($allTasks, $project);
    }

    private function filterByProject(array $tasks, Project $project): array{
        $result = array();
        foreach ($tasks as $task){
            if ($task->getProject()->getToken() === $project->getToken()){
                array_push($result, $task);
            }
        }
        return $result;
//        return $project->getOwner()->getEmail() === $user->getEmail();
    }
}