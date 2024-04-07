<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Headshot;
use App\Entity\TeamName;
use App\Repository\TeamRepository;
use App\Repository\TeamNameRepository;
use App\Repository\HeadshotRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class SearchController extends AbstractController
{

    public function __construct(private readonly ManagerRegistry $doctrine) {}

    #[Route(path: '/search.{_format}', name: 'search_search', format: 'html', requirements: ['_format' => 'html|json'])]
    public function search(Request $request): Response
    {
        $headshots = null;
        $teams = null;
        $query = $request->query->get('q');

        if (!empty($query)) {
            $query = trim($query, "% \n\r\t\v\0");
            if (strlen($query) >= 3) {
                /** @var HeadshotRepository */
                $headshotRepo = $this->doctrine->getRepository(Headshot::class);
                $headshots = $headshotRepo->searchByPersonName($query, 200);

                /** @var TeamRepository */
                $teamRepo = $this->doctrine->getRepository(Team::class);
                $teams = $teamRepo->searchByName($query, 200);
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
        } else {
            throw new NotAcceptableHttpException('Unknown format: '.$format);
        }
    }

    #[Route(path: '/search/teams.json', name: 'search_teams_json', format: 'json')]
    public function listTeamsJson(Request $request): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        $teams = $teamRepo->findAllAlphabetical();

        $response = [];
        foreach ($teams as $team) {
            $response[] = [
                'name' => $team->getName(),
                'slug' => $team->getSlug(),
            ];
        }

        /** @var TeamNameRepository */
        $teamNameRepo = $this->doctrine->getRepository(TeamName::class);
        $teamNames = $teamNameRepo->findAllAlphabetical();

        foreach ($teamNames as $tn) {
            $team = $tn->getTeam();

            // no point including names that match the team's primary name
            if ($tn->getName() == $team->getName()) continue;

            $response[] = [
                'name' => $tn->getName(),//." (".$team->getName().")",
                'slug' => $team->getSlug(),
            ];
        }

        return $this->json(['teams' => $response]);
    }
}
