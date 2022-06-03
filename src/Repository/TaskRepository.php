<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    protected $id;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }


    public function getAvaiableTaskWithFilter(int $id, array $filter, Bool $hasAdmin = false): array
    {

        if($filter['due_date'] or $filter['due_date'] === null)
        {
            $duedate = array('dueDate' => 'desc');
        }
        else
        {
            $duedate = array('dueDate' => 'asc');
        }

        foreach ($filter as $key => $value)
        {
            if($key === null)
            {
                unset($filter[$key]);
            }
        }

        unset($filter['due_date']);
        if($hasAdmin)
        {
            return $this->getEntityManager()->getRepository(Task::class)->findBy($filter, $duedate);
        }
        else
        {
            $this->id = $id;
            $tasks = $this->getEntityManager()->getRepository(Task::class)->findBy($filter, $duedate);

            return array_filter($tasks, function ($task) {
                return $this->id === $task->getAuthor()->getId() || $this->id === $task->getProject()->getAuthor()->getId();
            });
        }
    }

    public function getAvaiableTask(int $id, Bool $hasAdmin = false): array
    {
        if($hasAdmin)
        {
            return $this->getEntityManager()->getRepository(Task::class)->findAll();
        }
        else
        {
            $this->id = $id;
            $tasks = $this->getEntityManager()->getRepository(Task::class)->findAll();

            return array_filter($tasks, function ($task) {
                return $this->id === $task->getAuthor()->getId() || $this->id === $task->getProject()->getAuthor()->getId();
            });
        }
    }

}