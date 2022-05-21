<?php

namespace App\Controller;

use App\Entity\Project;
use App\Type\ProjectType;
use App\Entity\Task;
use App\Type\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Type\TaskFilterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Component\Security\Core\Security;

class ProjectController extends AbstractController
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/project/list", name="project_list")
     */
    public function showPorjects(Request $request): Response
    {
        $user = $this->getUser();

        if($this->isGranted('ROLE_ADMIN'))
        {
            /** @var $projects */
            $projects = $this->getDoctrine()->getManager()
                ->getRepository(Project::class)
                ->findBy([], []);
        }
        else
        {
            /** @var $projects */
            $projects = $this->getDoctrine()->getManager()
                ->getRepository(Project::class)
                ->findBy(['author' => $user->getId()]);
        }



        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * @Route("/project/create", name="project_create")
     */
    public function createProject(Request $request): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);
        $user = $this->getUser();


        if ($form->isSubmitted() && $form->isValid()) {



            $project->setToken(strval(random_int(10000, 99999)));

            $project->setAuthor($user->getId());

            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('project_list');
        }

        return $this->render('project/createForm.html.twig', [
            'form' => $form->createView(),
    ]);
    }


    /**
     * @Route("/project/{id}/", name="project_byId")
     */
    public function projectById($id): Response
    {
        /** @var Task $project */
        $project = $this->getDoctrine()->getManager()->find(Project::class, $id);

        if ($project === null) {
            throw $this->createNotFoundException(sprintf("Project with id %s not found", $id));
        }

        $user = $this->getUser();

        if(!$this->isGranted('ROLE_ADMIN'))
        {
            if($user->getId() !== $project->getAuthor())
            {
                throw $this->createAccessDeniedException();
            }
        }

        $tasks = $this->getDoctrine()->getRepository(Task::class)
            ->findBy(['project' => $id], []);

        return $this->render('project/project.html.twig',[
            'id' => $id,
            'tasks' => $tasks,
        ]);
    }


    /**
     * @Route("/project/{id}/edit", name="project_edit_byId")
     */
    public function editProject(Request $request, $id): Response
    {
        $project = $this->getDoctrine()->getManager()->find(Project::class, $id);

        $user = $this->getUser();

        if(!$this->isGranted('ROLE_ADMIN'))
        {
            if($user->getId() !== $project->getAuthor())
            {
                throw $this->createAccessDeniedException();
            }
        }

        $form = $this->createForm(ProjectType::class, $project);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('project_list');
        }

        return $this->render("project/project_edit.html.twig", [
            'form' => $form->createView(),
            'id' => $id,
        ]);
    }
}
