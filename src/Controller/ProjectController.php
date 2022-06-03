<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Type\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @Route("/project/list", name="project_list")
     */
    public function index(): Response
    {
        $projects = $this->getDoctrine()
            ->getManager()
            ->getRepository(Project::class)
            ->getAvailableProject($this->getUser()->getId(),$this->isGranted('ROLE_ADMIN'));

        return $this->render('project/index.html.twig', [
            'projects' => $projects
        ]);
    }

    /**
     * @Route("/project/create/form", name="project_create_form")
     */
    public function createProjectForm(Request $request): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $project->setAuthor($this->getUser());
            $project->setToken();
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute("project_list");
        }

        return $this->render('project/create_project_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/project/{id}", name="project_byId")
     */
    public function projectById($id): Response
    {
        $project = $this->getDoctrine()->getManager()->find(Project::class, $id);
        if($project === null)
            throw $this->createNotFoundException("Project with %s not found", $id);

        $this->denyAccessUnlessGranted('project_view', $project);


        $tasks = $this->getDoctrine()->getRepository(Task::class)
            ->findBy(['project' => $project]);
        return $this->render('project/project.html.twig', [
            'project' => $project,
            'tasks' => $tasks
        ]);
    }

    /**
     * @Route("/project/{id}/edit", name="project_byId_edit")
     */
    public function projectEdit($id, Request $request): Response
    {
        $project = $this->getDoctrine()->getManager()->find(Project::class, $id);
        if($project === null)
            throw $this->createNotFoundException("Project with %s not found", $id);
        $this->denyAccessUnlessGranted('project_edit', $project);

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute("project_list");
        }

        return $this->render('project/project_edit_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
