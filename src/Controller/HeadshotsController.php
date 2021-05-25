<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Team;

class HeadshotsController extends AbstractController
{
    /**
     * @Route("/headshots/teams")
     */
    public function listTeams(): Response
    {
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findAll();

        if (!$teams) {
            throw new \Exception('Could not find teams');
        }

        return $this->render('headshots/teams.html.twig', ['teams' => $teams]);
    }

    /**
     * @Route("/headshots/{slug}")
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

        return $this->render('headshots/team.html.twig', ['team' => $team]);
    }
}
