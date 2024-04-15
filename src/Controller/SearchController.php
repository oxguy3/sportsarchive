<?php

namespace App\Controller;

use App\Entity\Headshot;
use App\Entity\Team;
use App\Repository\HeadshotRepository;
use App\Repository\TeamRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SearchController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine) {}

    #[Route(path: '/search.{_format}', name: 'search_search', format: 'html', requirements: ['_format' => 'html|json'])]
    public function search(Request $request): Response
    {
        $headshots = null;
        $teamResults = null;
        $query = $request->query->get('q');

        if (!empty($query)) {
            $query = trim($query, "% \n\r\t\v\0");
            if (strlen($query) >= 3) {
                /** @var HeadshotRepository */
                $headshotRepo = $this->doctrine->getRepository(Headshot::class);
                $headshots = $headshotRepo->searchByPersonName($query, 200);

                /** @var TeamRepository */
                $teamRepo = $this->doctrine->getRepository(Team::class);
                $teamResults = $teamRepo->searchByName($query, 200);
            }
        }

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('search/search.html.twig', [
                'query' => $query,
                'headshots' => $headshots,
                'teamResults' => $teamResults,
            ]);
        } elseif ($format == 'json') {
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
                    ],
                ],
            ]);
            $normalTeamResults = $serializer->normalize($teamResults, null, [
                AbstractNormalizer::ATTRIBUTES => [
                    'team' => [
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
                    ],
                    'names' => [
                        'name',
                        'type',
                        'language',
                        'firstSeason',
                        'lastSeason',
                    ],
                ],
            ]);
            $jsonContent = $serializer->serialize(
                [
                    'headshots' => $normalHeadshots,
                    'teamResults' => $normalTeamResults,
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
        $results = [];
        $query = $request->query->get('q');

        if (!empty($query)) {
            $query = trim($query, "% \n\r\t\v\0");
            if (strlen($query) >= 3) {
                /** @var TeamRepository */
                $teamRepo = $this->doctrine->getRepository(Team::class);
                $results = $teamRepo->searchByName($query, 200);
            }
        }
        // dump($entities);

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $normalResults = $serializer->normalize($results, null, [
            AbstractNormalizer::ATTRIBUTES => [
                'team' => [
                    'name',
                    'slug',
                    'type',
                ],
                'names' => [
                    'name',
                    'type',
                    'language',
                    'firstSeason',
                    'lastSeason',
                ],
            ],
        ]);
        $jsonContent = $serializer->serialize(
            ['results' => $normalResults],
            'json'
        );

        return JsonResponse::fromJsonString($jsonContent);
    }
}
