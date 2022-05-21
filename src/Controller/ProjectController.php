<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @Route("/projects", name="project_list")
     */
    public function list(): Response
    {
        $projects = $this->getDoctrine()->getManager()
                ->getRepository(Project::class)->findBy([]);
        return $this->render('project/list.html.twig', [
            'projects' => $projects,
        ]);
    }


    /**
     * @Route("/projects/create", name="project_create")
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
}
