<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    private function getFilter($filters = []): array
    {
        if ($filters["dueDate"] === null)
            $dueDate = array("dueDate" => "DESC");
        else
            $dueDate = array("dueDate" => "ASC");
        unset($filters["dueDate"]);

        if ($filters["project"] === null)
            unset($filters["project"]);

        if ($filters["author"] === null)
            unset($filters["author"]);

        if ($filters["isCompleted"] === null)
            unset($filters["isCompleted"]);

        return $this->getEntityManager()->getRepository(Task::class)->findBy($filters, $dueDate);
    }

    public function getByFilter($filters = []): array
    {
        if (!empty($filters)) {
            return $this->getFilter($filters);
        }

        return $this->getEntityManager()->getRepository(Task::class)->findAll();
    }

}