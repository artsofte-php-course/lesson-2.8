<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Type\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends AbstractController
{


    /**
     * @Route("/projects", name="project_list")
     * @return Response
     */
    public function list(Request $request): Response
    {
        $user = $this->getUser();

        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            $projects = $this->getDoctrine()->getManager()->getRepository(Project::class)->findAll();
        } else {
            $projects = $this->getDoctrine()->getManager()->getRepository(Project::class)->findBy(['owner' => $user]);
        }


        return $this->render('project/list.html.twig', [
            'projects' => $projects
        ]);
    }

    /**
     *
     * @Route("/projects/add", name="project_add")
     * @param Request $request
     * @return Response
     */
    public function add(Request $request): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $project->setOwner($this->getUser());

            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('project_list');
        }

        return $this->render("project/add.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/projects/{slug}", name="project_show")
     */
    public function show($slug): Response
    {
        $project = $this->getDoctrine()->getManager()->find(Project::class, $slug);

        $tasks = $this->getDoctrine()->getRepository(Task::class)->findBy(['project' => $project]);

        return $this->render('project/project.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    /**
     * @Route("/projects/{slug}/remove", name="project_remove")
     */
    public function remove($slug): Response
    {
        $e = $this->getDoctrine()->getManager();

        $project = $this->getDoctrine()->getRepository(Project::class)->findOneBy(["sign" => $slug]);
//        dd($project);
        $e->remove($project);
        $e->flush();

        return $this->redirectToRoute("project_list");
    }

    /**
     * @Route("/projects/{slug}/edit", name="project_edit")
     */
    public function edit($slug, Request $request): Response
    {
        $project = $this->getDoctrine()->getRepository(Project::class)->findOneBy(["sign" => $slug]);

        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute("project_list");
        }

        return $this->render("project/edit.html.twig", [
            "form" => $form->createView(),
            "slug" => $slug
        ]);
    }
}