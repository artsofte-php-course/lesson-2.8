<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Type\ProjectType;
use App\Type\TaskFilterType;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use const http\Client\Curl\PROXY_HTTP;

class ProjectController extends AbstractController
{

    /**
     * @Route("/projects", name="project_list")
     * @return Response
     */

    public function list(Request $request): Response
    {
        $user = $this -> getUser();

        if (in_array("ROLE_ADMIN", $user -> getRoles())) {
            $projects = $this -> getDoctrine() -> getManager() -> getRepository(Project::class) -> findAll();
        } else {
            $projects = $this -> getDoctrine() -> getManager() -> getRepository(Project::class) -> findBy(['owner' => $user]);
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
        ]);
    }

    /**
     * @Route("/projects/{slug}", name="project_info")
     * @return Response
     */
    public function info($slug, Request $request): Response
    {
        $project = $this -> getDoctrine() -> getRepository(Project::class) ->
        findBy(["projectKey" => $slug]);

        $tasks = $this -> getDoctrine() -> getRepository(Task::class) -> findBy(["project" => $project]);

        return $this->render('task/project_tasks.html.twig', [
            'tasks' => $tasks,
            "project_name" => $project[0] -> getName(),
        ]);
    }

}