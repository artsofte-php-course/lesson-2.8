<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Type\TaskFilterType;
use App\Type\TaskType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

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
        $option = [
            'userId' => $this->getUser()->getId(),
            'hasAdmin' => $this->isGranted('ROLE_ADMIN'),
        ];
        $form = $this->createForm(TaskType::class, $task, $option);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $task->setAuthor($this->getUser());

            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('task_list');
        }

        return $this->render("task/create.html.twig", [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/tasks", name="task_list")
     * @return Response
     */
    public function list(Request $request): Response
    {
        $user = $this->getUser();
        $hasAdmin = $this->isGranted('ROLE_ADMIN');

        $ids = $this->getProjectID($user->getId());

        $taskFilterForm = $this->createForm(TaskFilterType::class);

        $taskFilterForm->handleRequest($request);

        if ($taskFilterForm->isSubmitted() && $taskFilterForm->isValid()) {

            $filter = $taskFilterForm->getData();
            $tasks = $this->getDoctrine()->
            getRepository(Task::class)->
            getAvailableTasksByFilter($this->getUser()->getId(), $hasAdmin, $filter);

        } else {
            $sql = array('project' => $ids);
            if($hasAdmin)
            {
                unset($sql['project']);
            }

            $tasks = $this->getDoctrine()->getManager()
                ->getRepository(Task::class)
                ->findBy($sql, [
                    'dueDate' => 'DESC'
                ]);

        }


        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
            'filterForm' => $taskFilterForm->createView()
        ]);
    }

    /**
     * @Route("/tasks/{id}/complete", name="task_complete")
     * @IsGranted("ROLE_USER")
     * swap true and false
     * @return Response
     */
    public function swapValueCompleteTask($id): Response
    {
        /** @var Task $task */
        $task = $this->getDoctrine()->getManager()->find(Task::class, $id);

        $this->denyAccessUnlessGranted('complete', $task);

        if ($task === null) {
            throw $this->createNotFoundException(sprintf("Task with id %s not found", $id));
        }

        $task->swapValueIsCompleted();

        $this->getDoctrine()->getManager()->persist($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('task_list');
    }

    private function getProjectID(int $id)
    {
        $returnId = [];

        /** @var $projects */
        $projects = $this->getDoctrine()->getManager()
            ->getRepository(Project::class)
            ->findBy(['author' => $id]);

        foreach ($projects as $project)
        {
            array_push($returnId, $project->getId());
        }

        return $returnId;
    }


    /**
     * @Route("/tasks/{id}/edit", name="task_edit_byId")
     * @return Response
     */
    public function editTask(Request $request, $id): Response
    {
        $task = $this->getDoctrine()->getManager()->find(Task::class, $id);

        $this->denyAccessUnlessGranted('task_edit', $task);

        $option = [
            'userId' => $this->getUser()->getId(),
            'hasAdmin' => $this->isGranted('ROLE_ADMIN'),
        ];
        $form = $this->createForm(TaskType::class, $task, $option);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('task_list');
        }

        return $this->render("task/task_edit_form.html.twig", [
            'form' => $form->createView(),
            'id' => $id,
        ]);
    }
}