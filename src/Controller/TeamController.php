<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Team;
use App\Form\TeamType;

class TeamController extends AbstractController
{
    /**
     * @Route("/teams", name="team_list")
     */
    public function listTeams(): Response
    {
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findAllAlphabetical();

        return $this->render('team/list.html.twig', ['teams' => $teams]);
    }

    /**
     * @Route("/teams/new", name="team_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createTeam(Request $request): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$team` variable has also been updated
            $team = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('team_list');
        }

        return $this->render('team/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/teams/{slug}", name="team_show")
     */
    public function showTeam(string $slug): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException(
                'No team found for slug '.$slug
            );
        }

        return $this->render('team/show.html.twig', ['team' => $team]);
    }
}
