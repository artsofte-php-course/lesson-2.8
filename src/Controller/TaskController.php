<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
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
        $projectRepository = $this -> getDoctrine() -> getRepository(Project::class);

        $form = $this->createForm(TaskType::class, $task, [
            "projectsList" => $projectRepository -> findByUserRole($user -> getId()),
//            'userId' => 4,
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
            "action" => "create",
        ]);
    }

    /**
     * @Route("/tasks", name="task_list")
     * @return Response
     */
    public function list(Request $request): Response
    {
        $user = $this -> getUser();
        $userIsAdmin = $this -> isGranted("ROLE_ADMIN");

        $projectRepository = $this -> getDoctrine() -> getRepository(Project::class);
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $projectsList = $projectRepository -> findByUserRole($user -> getId());
        $projectsIdList = array_map(function ($item) { return $item->getId(); }, $projectsList);

        $taskFilterForm = $this->createForm(TaskFilterType::class, options: [
            "projectsList" => $projectsList,
            "authorsList" => $userRepository->findByTaskAuthorshipInProjectList($projectsIdList),
            "ownersList" => $userRepository->findByProjectOwnershipInProjectList($projectsIdList),
        ]);

        $taskFilterForm->handleRequest($request);
        $taskRepository = $this->getDoctrine()->getRepository(Task::class);

        if ($taskFilterForm->isSubmitted() && $taskFilterForm->isValid()) {
            $tasks = $taskRepository -> findByFilterData($taskFilterForm->getData(),
                !($this->isGranted("ROLE_ADMIN")) ? $this->getUser()->getId() : null);
        } else {
            $tasks = $userIsAdmin ?
                $taskRepository -> findBy([], ["dueDate" => "DESC"]) :
                $taskRepository -> findByProjectOwnerId($user -> getId());
//            dd($tasks);
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

        $this->denyAccessUnlessGranted('edit', $task);

        if ($task === null) {
            throw $this->createNotFoundException(sprintf("Task with id %s not found", $id));
        }

        $user = $this -> getUser();
        $projectRepository = $this -> getDoctrine() -> getRepository(Project::class);

        $form = $this->createForm(TaskType::class, data: $task, options: [
            "projectsList" => $projectRepository -> findByUserRole($user -> getId()),
        ]);

        $form -> handleRequest($request);

        if ($form -> isSubmitted() && $form -> isValid()) {
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('task_list');
        }

        return $this -> render('task/create.html.twig', [
            "form" => $form -> createView(),
            "action" => "edit",
            "id" => $id
        ]);
    }
}