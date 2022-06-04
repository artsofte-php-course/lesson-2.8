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
            'userRole' => $this->getUser()->getRoles(),
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
        $Flag=False;
        $user = $this->getUser();
        foreach ($user->getRoles() as $role)
        {
            if($role === 'ROLE_ADMIN')
            {
                $Flag=True;
            }
        }
        if(!$Flag)
        {
            $ids = $this->getProjectID($user->getId());
        }

        $taskFilterForm = $this->createForm(TaskFilterType::class);

        $taskFilterForm->handleRequest($request);

        if ($taskFilterForm->isSubmitted() && $taskFilterForm->isValid()) {

            $filter = $taskFilterForm->getData();
            if ($filter['isCompleted'] === null) {
                unset($filter['isCompleted']);
            }
            if(!$Flag)
            {
                $filter['project'] = $ids;
            }
            $tasks = $this->getDoctrine()->getRepository(Task::class)
                ->findBy($filter, [
                    'dueDate' => 'DESC'
                ]);

        } else {
            if($Flag)
            {
                /** @var $tasks */
                $tasks = $this->getDoctrine()->getManager()
                    ->getRepository(Task::class)
                    ->findBy([], [
                        'dueDate' => 'DESC'
                    ]);
            }
            else
            {
                /** @var $tasks */
                $tasks = $this->getDoctrine()->getManager()
                    ->getRepository(Task::class)
                    ->findBy(['project' => $ids], [
                        'dueDate' => 'DESC'
                    ]);
            }
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

        $this->denyAccessUnlessGranted('complete', $task);

        if ($task === null) {
            throw $this->createNotFoundException(sprintf("Task with id %s not found", $id));
        }

        $task->setIsCompleted(true);

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
}