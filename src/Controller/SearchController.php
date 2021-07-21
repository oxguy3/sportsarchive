<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Team;
use App\Entity\Headshot;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;

class SearchController extends AbstractController
{
    /**
     * @Route(
     *      "/search.{_format}",
     *      name="search_search",
     *      format="html",
     *      requirements={"_format": "html|json"}
     * )
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

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('search/search.html.twig', [
                'query' => $query,
                'headshots' => $headshots,
                'teams' => $teams,
            ]);

        } else if ($format == 'json') {
            $encoders = [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);
            $normalHeadshots = $serializer->normalize($headshots, null, [
                AbstractNormalizer::ATTRIBUTES => [
                    'personName',
                    'jerseyNumber',
                    'filename',
                    'role',
                    'title',
                    'roster' => [
                        'year',
                        'teamName',
                    ]
                ]
            ]);
            $normalTeams = $serializer->normalize($teams, null, [
                AbstractNormalizer::ATTRIBUTES => [
                    'name',
                    'slug',
                    'type',
                    'logoFileType',
                    'website',
                    'country',
                    'startYear',
                    'endYear',
                    'gender',
                    'sport',
                ]
            ]);
            $jsonContent = $serializer->serialize(
                [
                    'headshots' => $normalHeadshots,
                    'teams' => $normalTeams,
                ],
                'json'
            );

            return JsonResponse::fromJsonString($jsonContent);
        }
    }

    /**
     * @Route(
     *      "/search/teams.json",
     *      name="search_teams_json",
     *      format="json"
     * )
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
