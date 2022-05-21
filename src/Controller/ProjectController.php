<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Type\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use const http\Client\Curl\PROXY_HTTP;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{

    /**
     * @Route("/projects", name="project_list")
     * @return Response
     */

    public function list(Request $request): Response
    {
        $user = $this -> getUser();
        $project_repository = $this -> getDoctrine() -> getManager() -> getRepository(Project::class);

        if (in_array("ROLE_ADMIN", $user -> getRoles())) {
            $projects = $project_repository -> findAll();
        } else {
            $projects = $project_repository -> findBy(['owner' => $user]);
        }
        return $this -> render("projects/list.html.twig", [
            "projects" => $projects,
        ]);
    }

    /**
     * @Route("/projects/create", name="project_create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $project = new Project();
        $form = $this -> createForm(ProjectType::class, $project);

        $form -> handleRequest($request);

        if ($form -> isSubmitted() && $form -> isValid()) {

            $project -> setOwner($this -> getUser());

            $this -> getDoctrine() -> getManager() -> persist($project);
            $this -> getDoctrine() -> getManager() -> flush();

            return $this -> redirectToRoute("project_list");
        }

        return $this -> render("projects/create.html.twig", [
            "form" => $form -> createView(),
            "action" => "creating"
        ]);
    }

    /**
     * @Route("/projects/{slug}", name="project_info")
     * @return Response
     */
    public function info($slug, Request $request): Response
    {
        $project = $this -> getDoctrine() -> getManager() -> find(Project::class, $slug);

        $tasks = $this -> getDoctrine() -> getRepository(Task::class) -> findBy(["project" => $project]);

        return $this->render('task/project_tasks.html.twig', [
            'tasks' => $tasks,
            "project_name" => $project -> getName(),
        ]);
    }

    /**
     * @Route("/projects/{slug}/edit", name="project_edit")
     * @param $slug
     * @param Request $request
     * @return Response
     */
    public function edit($slug, Request $request): Response
    {
        $project = $this->getDoctrine()->getRepository(Project::class) -> findBy(["projectKey" => $slug])[0];

        $form = $this->createForm(ProjectType::class, $project);

        $form -> handleRequest($request);

        if ($form -> isSubmitted() && $form -> isValid()) {
            $this -> getDoctrine() -> getManager() -> persist($project);
            $this -> getDoctrine() -> getManager() -> flush();

            return $this -> redirectToRoute("project_list");
        }

        return $this->render("projects/create.html.twig", [
            "form" => $form->createView(),
            "action" => "editing",
            "slug" => $slug
        ]);
    }

    /**
     * @Route("/projects/{slug}/delete", name="project_delete")
     * @param $slug
     * @param Request $request
     * @return Response
     */
    public function delete($slug, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $project = $this->getDoctrine()->getRepository(Project::class) -> findBy(["projectKey" => $slug])[0];

        $em -> remove($project);
        $em -> flush();

        return $this->redirectToRoute("project_list");
    }
}