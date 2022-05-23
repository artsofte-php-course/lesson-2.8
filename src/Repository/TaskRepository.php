<?php

namespace App\Repository;

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


    /**
     * @param $id
     * @param $hasAdmin - default value FALSE
     * @param $filter - default value null
     * @return array
     */
    public function getAvailableTasksByFilter($id, $hasAdmin = false, $filter = []): array
    {
        if(isset($filter) and $hasAdmin)
        {
            return $this->getTasksByFilter($filter);
        }
        if(isset($filter) and !$hasAdmin)
        {
            $tasks = $this->getTasksByFilter($filter);
            foreach ($tasks as $key => $task)
            {
                if($task->getProject()->getAuthor() !== $id)
                    unset($tasks[$key]);
            }
            return $tasks;
        }

        if($hasAdmin)
            return $this->getEntityManager()->getRepository(Task::class)->findAll();
        if(!$hasAdmin)
        {
            $tasks = $this->getEntityManager()->getRepository(Task::class)->findAll();
            foreach ($tasks as $key => $task)
            {
                if($task->getProject()->getAuthor() !== $id and $task->getAuthor() !== $id)
                    unset($tasks[$key]);

            }
            return $tasks;
        }

        $tasks = [];
        return $tasks;
    }

    private function getTasksByFilter($filter): array
    {
        //region unset null value
        if($filter['isCompleted'] === null)
            unset($filter['isCompleted']);
        if($filter['due_date'] or $filter['due_date'] === null)
            $duedate = array('dueDate' => 'desc');
        else
            $duedate = array('dueDate' => 'asc');
        unset($filter['due_date']);
        if($filter['project'] === null)
            unset($filter['project']);
        if($filter['author'] === null)
            unset($filter['author']);
        //endregion

        return $this->getEntityManager()->getRepository(Task::class)->findBy($filter, $duedate);
    }

}