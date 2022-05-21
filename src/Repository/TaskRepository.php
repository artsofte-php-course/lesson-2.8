<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @throws Exception
     */
    public function getTasksByProjectOwnerId(int $id) : array
    {
        $conn = $this -> getEntityManager() -> getConnection();

        $sql = "
            SELECT t.id, t.name, t.description, t.due_date as dueDate, 
            u.email as author, t.is_completed as isCompleted
            FROM task as t INNER JOIN user as u ON t.author_id = u.id
            WHERE project_id in (SELECT id FROM project WHERE owner_id = :id);
        ";
        $stmt = $conn -> prepare($sql);
        return $stmt ->executeQuery(["id" => $id]) -> fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    public function getTasksAuthors() : array
    {
        $conn = $this -> getEntityManager() -> getConnection();

        $sql = "
            SELECT DISTINCT u.email, u.id 
            FROM task as t inner join user as u
            on t.author_id = u.id;
        ";
        $stmt = $conn -> prepare($sql);
        return $stmt ->executeQuery() -> fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    public function getTasksAuthorsByProjectOwnerId(int $id) : array
    {
        $conn = $this -> getEntityManager() -> getConnection();

        $sql = "
            SELECT DISTINCT u.email, u.id 
            FROM task as t INNER JOIN user as u ON t.author_id = u.id
            WHERE project_id in (SELECT id FROM project WHERE owner_id = :id);
        ";
        $stmt = $conn -> prepare($sql);
        return $stmt ->executeQuery(["id" => $id]) -> fetchAllKeyValue();
    }


    /**
     * @throws Exception
     */
    public function findByFilters(array $filters, array $orders=null) : array
    {
        $conn = $this -> getEntityManager() -> getConnection();

        $sql = "SELECT t.id, t.name, t.description, t.due_date as dueDate, 
            t.author_id as author, t.is_completed as isCompleted 
            from task as t
            inner join project as p
            on t.project_id = p.id";

        if (count($filters) !== 0) {
            $sql .= " where " . implode(" and ", $filters);
        }

        if ($orders !== null) {
            $sql .= " order by t.due_date " . $orders["dueDate"] . ", t.name " . $orders["name"];
        }

        $sql .= ";";

        $stmt = $conn -> prepare($sql);
        return $stmt ->executeQuery() -> fetchAllAssociative();
    }


}