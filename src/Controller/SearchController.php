<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Team;
use App\Entity\Headshot;

class SearchController extends AbstractController
{
    /**
     * @Route("/search", name="search_search")
     */
    public function search(Request $request): Response
    {
        $headshots = null;
        $teams = null;
        $query = $request->query->get('q');

        if (!empty($query)) {
            $query = trim($query, "% \n\r\t\v\0");
            if (strlen($query) >= 3) {
                $headshots = $this->getDoctrine()
                    ->getRepository(Headshot::class)
                    ->searchByPersonName($query, 200);

                $teams = $this->getDoctrine()
                    ->getRepository(Team::class)
                    ->searchByName($query, 200);
            }
        }

        return $this->render('headshot/headshotSearch.html.twig', [
            'query' => $query,
            'headshots' => $headshots,
            'teams' => $teams,
            'imageUrlInfix' => $_ENV['S3_HEADSHOTS_BUCKET'].'/'.$_ENV['S3_PREFIX'],
        ]);
    }

    /**
     * @Route("/search/teams.json", name="search_teams_json")
     */
    public function listTeamsJson(Request $request): Response
    {
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findAllAlphabetical();

        $response = [];
        foreach ($teams as $team) {
            $response[] = [
                'name' => $team->getName(),
                'slug' => $team->getSlug(),
            ];
        }

        return $this->json(['teams' => $response]);
    }
}
