<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Type\TaskFilterType;
use App\Type\TaskType;
use Doctrine\Common\Collections\Criteria;
use phpDocumentor\Reflection\Types\Integer;
use PhpParser\Node\Expr\Array_;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use const http\Client\Curl\PROXY_HTTP;

class TaskController extends AbstractController
{
    /**
     *
     * @Route("/tasks/create", name="task_create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $task = new Task();
        $user = $this -> getUser();
        $project_repository = $this -> getDoctrine() -> getRepository(Project::class);

        $form = $this->createForm(TaskType::class, $task, [
            "projects_list" => in_array("ROLE_ADMIN", $user -> getRoles()) ?
                $project_repository -> findAll() :
                $project_repository -> findBy(["owner" => $user])
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setAuthor($this->getUser());

            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('task_list');
        }

        return $this->render("task/create.html.twig", [
            'form' => $form->createView(),
            "action" => "creating",
        ]);
    }

    /**
     * @Route("/tasks", name="task_list")
     * @return Response
     */
    public function list(Request $request): Response
    {
        $user = $this -> getUser();
        $user_is_admin = in_array("ROLE_ADMIN", $user -> getRoles());

        $project_repository = $this -> getDoctrine() -> getRepository(Project::class);
        $task_repository = $this->getDoctrine()->getRepository(Task::class);

        $projects_list = $user_is_admin ?
            $project_repository -> findAll() :
            $project_repository -> findBy(["owner" => $user]);

        $taskFilterForm = $this->createForm(TaskFilterType::class, options: [
            "projects_list" => $projects_list,
            "authors_list" => $user_is_admin ?
                $task_repository ->getTasksAuthors() :
                $task_repository -> getTasksAuthorsByProjectOwnerId($user -> getId()),
            "owners_list" => $user_is_admin ?
                $project_repository -> getProjectOwners() :
                Array($user -> getUsername() => $user -> getId()),
        ]);

        $taskFilterForm->handleRequest($request);

        if ($taskFilterForm->isSubmitted() && $taskFilterForm->isValid()) {

            $data = $taskFilterForm->getData();

            $filters = Array();
//            dd($data);
            if ($data["isCompleted"] !== null) {
                $filters[] = "t.is_completed=" . (int)$data["isCompleted"];
            }

            if ($data["filter-by-project"]) {
                $filters[] = "t.project_id=" . $data["project"] -> getId();
            }

            if ($data["filter-by-author"]) {
                $filters[] = "t.author_id=" . $data["author"];
            }

            if ($data["filter-by-owner"]) {
                $filters[] = "p.owner_id=" . $data["project-owner"];
            }

            $tasks = $task_repository -> findByFilters($filters,
                orders: [
                    "dueDate" => $data["sort-by-date"] ? "ASC" : "DESC",
                    "name" => $data["sort-by-name"] ? "ASC" : "DESC"
                ]);

        } else {
            $tasks = $user_is_admin ?
                $task_repository -> findBy([], ["dueDate" => "DESC"]) :
                $task_repository -> getTasksByProjectOwnerId($user -> getId());
        }

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'filterForm' => $taskFilterForm->createView(),
        ]);
    }

    /**
     * @Route("/tasks/{id}/complete", name="task_complete")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function complete($id): Response
    {
        /** @var Task $task */
        $task = $this->getDoctrine()->getManager()->find(Task::class, $id);

        $this->denyAccessUnlessGranted('complete', $task);

        if ($task === null) {
            throw $this->createNotFoundException(sprintf("Task with id %s not found", $id));
        }

        $task->setIsCompleted(true);

        $this->getDoctrine()->getManager()->persist($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function edit($id, Request $request): Response
    {
        $task = $this->getDoctrine()->getManager()->find(Task::class, $id);

        $user = $this -> getUser();
        $project_repository = $this -> getDoctrine() -> getRepository(Project::class);

        $form = $this->createForm(TaskType::class, data: $task, options: [
            "projects_list" => in_array("ROLE_ADMIN", $user -> getRoles()) ?
                $project_repository -> findAll() :
                $project_repository -> findBy(["owner" => $user])
        ]);

        $form -> handleRequest($request);

        if ($form -> isSubmitted() && $form -> isValid()) {
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('task_list');
        }

        return $this -> render('task/create.html.twig', [
            "form" => $form -> createView(),
            "action" => "editing",
            "id" => $id
        ]);
    }
}