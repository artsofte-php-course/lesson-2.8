<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
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
    public function findByProjectOwnerId(int $id) : array
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

    public function getTasksAuthors() : array
    {
        return $this->createQueryBuilder("t")
            ->select(["distinct u.email", "u.id"])
            ->innerJoin("t.author", "u")
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @throws Exception
     */
    public function findByFilterData(array $data, ?int $user_id) : array
    {
        $qb = $this->createQueryBuilder("t");
        $qb->select("t");
        $qb->join("App\Entity\User", "u", "WITH", "t.author = u.id");
        $qb->join("App\Entity\Project", "p", "WITH", "t.project = p.id");

        if ($user_id !== null) {
            $qb->add("where", $qb->expr()->eq("p.owner", $user_id));
        }

        if ($data["project"] !== null) {
            $qb->add("where", $qb->expr()->eq("t.project", $data["project"]->getId()));
        }

        if ($data["author"] !== null) {
            $qb->add("where", $qb->expr()->eq("u.author", $data["author"]->getId()));
        }

        if ($data["project-owner"] !== null) {
            $qb->add("where", $qb->expr()->eq("p.owner", $data["owner"]->getId()));
        }

        $qb->orderBy("t.dueDate", $data["sort-by-date"] ? "ASC" : "DESC");
        $qb->orderBy("t.name", $data["sort-by-name"] ? "ASC" : "DESC");

        return $qb->getQuery()->getResult();
    }


}