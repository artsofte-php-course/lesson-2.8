<?php

namespace App\Controller;

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
        $option = ['userId' => $this->getUser()->getId(), 'hasAdmin' => $this->isGranted('ROLE_ADMIN')];
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
        $taskFilterForm = $this->createForm(TaskFilterType::class);

        $taskFilterForm->handleRequest($request);

        if ($taskFilterForm->isSubmitted() && $taskFilterForm->isValid()) {
            $filter = $taskFilterForm->getData();
            $tasks = $this->getDoctrine()
                ->getRepository(Task::class)
                ->getAvaiableTaskWithFilter($this->getUser()->getId(), $filter, $this->isGranted('ROLE_ADMIN'));
        } else {
            $tasks = $this->getDoctrine()
                ->getRepository(Task::class)
                ->getAvaiableTask($this->getUser()->getId(), $this->isGranted('ROLE_ADMIN'));
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
    public function complete($id): Response
    {
        /** @var Task $task */
        $task = $this->getDoctrine()->getManager()->find(Task::class, $id);

        if ($task === null) {
            throw $this->createNotFoundException(sprintf("Task with id %s not found", $id));
        }
        $this->denyAccessUnlessGranted('complete', $task);

        if($task->isCompleted())
        {
            $task->setIsCompleted();
        }
        else
        {
            $task->setIsCompleted(true);
        }


        $this->getDoctrine()->getManager()->persist($task);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('task_list');
    }
}