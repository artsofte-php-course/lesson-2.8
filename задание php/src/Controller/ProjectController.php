<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Task;
use App\Form\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Type\TaskFilterType;
use App\Type\TaskType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;


class ProjectController extends AbstractController
{
    /**
     * @Route("/projects", name="project_list")
     */
    public function list(): Response
    {
        $user = $this->getUser();
        $projects = $this->getDoctrine()->getManager()
                ->getRepository(Project::class)
                ->getAvailableProjects($user->getId(), $this->isGranted('ROLE_ADMIN'));
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

    /**
     * @Route("project/show={id}", name="show_porject")
     * @param $id
     * @return void
     */
    public function show($id): Response
    {
        $project = $this->getDoctrine()->getManager()->find(Project::class, $id);

        if ($project === null) {
            throw $this->createNotFoundException(sprintf("Project with id %s not found", $id));
        }

        $tasks = $this->getDoctrine()->getRepository(Task::class)
            ->findBy(['project' => $id], []);

        return $this->render('project/show.html.twig',[
            'id' => $id,
            'tasks' => $tasks,
        ]);
    }
}