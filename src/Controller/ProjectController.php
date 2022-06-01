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
     * @Route("/projects", name="listProject")
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
     * @Route("/project/create", name="createProject")
     */
    public function create(Request $request): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $project->setOwner($this->getUser());
            $project->setKeyProject();
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('listProject');
        }
        return $this->render("project/add.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/project/{keyProject}/change", name="changeProject")
     */
    public function change($keyProject, Request $request): Response
    {
        $project = $this->getDoctrine()->getRepository(Project::class)->findOneBy(["keyProject" => $keyProject]);
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute("listProject");
        }
        return $this->render("project/change.html.twig", [
            "keyProject" => $keyProject,
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/project/{keyProject}", name="infoProject")
     */
    public function projectInfo($keyProject): Response
    {
        $tasks = $this->getDoctrine()->getRepository(Task::class)
            ->findBy(['project' => $keyProject], []);
        return $this->render('project/project.html.twig', [
            'tasks' => $tasks,
            '$keyProject' => $keyProject
        ]);
    }




}