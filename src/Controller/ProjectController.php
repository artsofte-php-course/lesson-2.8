<?php

namespace App\Controller;

use App\Entity\Task;
use App\Type\TaskFilterType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Type\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Project;

class ProjectController extends AbstractController
{
    /**
     *
     * @Route("/project/create", name="project_create")
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
            $project->setToken(strval(random_int(1000000000,9999999999)));
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('project_list');
        }

        return $this->render("project/project-create.html.twig", [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/projects", name="project_list")
     * @IsGranted("ROLE_USER")
     * @return Response
     */
    public function list(Request $request): Response
    {
        $form = $this->createForm(ProjectType::class);

        $form->handleRequest($request);

        $projects = $this->getDoctrine()->getManager()
            ->getRepository(Project::class)
            ->findBy([], []);

        $user = $this->getUser();

        return $this->render('project/project-list.html.twig', [
            'projects' => $projects,
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}