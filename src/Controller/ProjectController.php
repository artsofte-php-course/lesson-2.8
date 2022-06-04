<?php

namespace App\Controller;

use App\Entity\Project;
use App\Type\ProjectType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends AbstractController
{
    /**
     *
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

            $project->setOwner($this->getUser());
            $project->setGeneratedKey();

            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('projects_show');
        }

        return $this->render("project/create.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     *
     * @Route("/projects", name="projects_show")
     * @return Response
     */
    public function show(Request $request): Response
    {
        $projects = $this->getDoctrine()->getRepository(Project::class)
            ->findBy([]);

        return $this->render("project/projectsList.html.twig", [
            'projects' => $projects
        ]);
    }
}