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
        $form = $this->createForm(TaskType::class, $task, ["data" => $this->getDoctrine()->getManager()
            ->getRepository(Project::class)->findAllByUser($this->getUser())]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

//            var_dump($form);

            $task->setAuthor($this->getUser());
            $task->setName($form->get('name')->getData());
            $task->setDueDate($form->get('dueDate')->getData());
            $task->setDescription($form->get('description')->getData());
            $task->setProject($this->getDoctrine()->getManager()
                ->getRepository(Project::class)->find($form->get('project')->getData()));

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
        $taskFilterForm = $this->createForm(TaskFilterType::class);

        $taskFilterForm->handleRequest($request);

        if ($taskFilterForm->isSubmitted() && $taskFilterForm->isValid()) {

            $filter = $taskFilterForm->getData();
            if ($filter['isCompleted'] === null) {
                unset($filter['isCompleted']);
            }

            $tasks = $this->getDoctrine()->getRepository(Task::class)
                ->findBy($filter, [
                    'dueDate' => 'DESC'
                ]);

        } else {
            /** @var $tasks */
            $tasks = $this->getDoctrine()->getManager()
                ->getRepository(Task::class)
                ->findBy([], [
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
     * @return Response
     */
    public function complete(Request $request): Response
    {
        $id = $request->get('id');
        $token = $request->get('token');

        /** @var Task $task */
        $task = $this->getDoctrine()->getManager()->find(Task::class, $id);

        $this->denyAccessUnlessGranted('complete', $task);

        if ($task === null) {
            throw $this->createNotFoundException(sprintf("Task with id %s not found", $id));
        }

        $task->setIsCompleted(true);

        $this->getDoctrine()->getManager()->persist($task);
        $this->getDoctrine()->getManager()->flush();

        if ($token !== null) {
            return $this->redirectToRoute('project_page', ["id" => $token]);
        }

        return $this->redirectToRoute('task_list');
    }
}