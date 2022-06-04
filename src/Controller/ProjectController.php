<?php

namespace App\Controller;

use App\Entity\Project;
use App\Type\ProjectType;
use App\Type\TaskFilterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends AbstractController
{
    /**
     * @Route("/projects/create", name="project_create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $project->setAuthor($this->getUser());

            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('project_list');
        }

        return $this->render("project/create.html.twig", [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/projects", name="project_list")
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

            $projects = $this->getDoctrine()->getRepository(Project::class)
                ->findBy($filter, [
                    'slugKey' => 'DESC'
                ]);

        } else {
            /** @var $projects */
            $projects = $this->getDoctrine()->getManager()
                ->getRepository(Project::class)
                ->findBy([], [
                    'slugKey' => 'DESC'
                ]);
        }

        return $this->render('project/list.html.twig', [
            'projects' => $projects,
            'filterForm' => $taskFilterForm->createView()
        ]);
    }





}