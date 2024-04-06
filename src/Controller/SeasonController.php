<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SportInfoProvider;
use App\Entity\Roster;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;

class SeasonController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine) {}

    /**
     * @Route(
     *      "/seasons.{_format}",
     *      name="season_sportpicker",
     *      format="html",
     *      requirements={"_format": "html|json"}
     * )
     */
    public function sportPicker(Request $request): Response
    {
        /** @var RosterRepository */
        $repo = $this->doctrine->getRepository(Roster::class);
        $sportCounts = $repo->findSportCounts();
        $totalCount = $repo->count([]);

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('season/seasonSportPicker.html.twig', [
                'sportCounts' => $sportCounts,
                'totalCount' => $totalCount,
            ]);

        } else if ($format == 'json') {
            return $this->json([
                'sportCounts' => $sportCounts,
                'totalCount' => $totalCount,
            ]);
        }
    }

    /**
     * @Route(
     *      "/seasons/all.{_format}",
     *      name="season_list",
     *      format="html",
     *      requirements={"_format": "html|json"}
     * )
     */
    public function listSeasonsAll(Request $request): Response
    {
        /** @var RosterRepository */
        $repo = $this->doctrine->getRepository(Roster::class);
        $seasons = $repo->findYears();

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('season/seasonList.html.twig', [
                'seasons' => $seasons,
                'sport' => null,
            ]);

        } else if ($format == 'json') {
            return $this->json(['seasons' => $seasons]);
        }
    }

    /**
     * Redirect for old URL format
     * 
     * @Route(
     *      "/seasons/{season}.{_format}",
     *      name="season_show_nosport",
     *      format="html",
     *      requirements={"season"="[\d-]+", "_format": "html|json"}
     * )
     */
    public function showSeasonNoSport(string $season): Response
    {
        return $this->redirectToRoute('season_show', [
            'season' => $season,
        ]);
    }

    /**
     * @Route(
     *      "/seasons/{sport}.{_format}",
     *      name="season_list_sport",
     *      format="html",
     *      requirements={"_format": "html|json"}
     * )
     */
    public function listSeasonsSport(Request $request, string $sport, SportInfoProvider $sportInfo): Response
    {
        if ($sport != '_none' && !$sportInfo->isSport($sport)) {
            throw $this->createNotFoundException('Unknown sport: '.$sport);
        }

        /** @var RosterRepository */
        $repo = $this->doctrine->getRepository(Roster::class);
        if ($sport != '_none') {
            $seasons = $repo->findYearsForSport($sport);
        } else {
            $seasons = $repo->findYearsForNoSport();
        }

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('season/seasonList.html.twig', [
                'seasons' => $seasons,
                'sport' => $sport,
            ]);

        } else if ($format == 'json') {
            return $this->json(['seasons' => $seasons]);
        }
    }

    /**
     * @Route(
     *      "/seasons/all/{season}.{_format}",
     *      name="season_show",
     *      format="html",
     *      requirements={"season"="[\d-]+", "_format": "html|json"}
     * )
     */
    public function showSeasonAll(Request $request, string $season): Response
    {
        /** @var RosterRepository */
        $repo = $this->doctrine->getRepository(Roster::class);
        $rosters = $repo->findByYear($season);

        if (!$rosters) {
            throw $this->createNotFoundException('No rosters found for season '.$season);
        }

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('season/seasonShow.html.twig', [
                'rosters' => $rosters,
                'season' => $season,
                'sport' => null,
            ]);

        } else if ($format == 'json') {
            $encoders = [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);
            $normalRosters = $serializer->normalize($rosters, null, [
                AbstractNormalizer::ATTRIBUTES => [
                    'year',
                    'teamName',
                    'team' => [
                        'name',
                        'slug',
                    ],
                ]
            ]);
            $jsonContent = $serializer->serialize(
                [
                    'season' => $season,
                    'rosters' => $normalRosters,
                ],
                'json'
            );

            return JsonResponse::fromJsonString($jsonContent);
        }
    }

    /**
     * @Route(
     *      "/seasons/{sport}/{season}.{_format}",
     *      name="season_show_sport",
     *      format="html",
     *      requirements={"season"="[\d-]+", "_format": "html|json"}
     * )
     */
    public function showSeasonSport(Request $request, string $sport, string $season, SportInfoProvider $sportInfo): Response
    {
        if ($sport != '_none' && !$sportInfo->isSport($sport)) {
            throw $this->createNotFoundException('Unknown sport: '.$sport);
        }

        /** @var RosterRepository */
        $repo = $this->doctrine->getRepository(Roster::class);
        $querySport = $sport != '_none' ? $sport : null;
        $rosters = $repo->findByYearForSport($season, $querySport);

        if (!$rosters) {
            throw $this->createNotFoundException('No rosters found for season '.$season.', sport '.$sport);
        }

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('season/seasonShow.html.twig', [
                'rosters' => $rosters,
                'season' => $season,
                'sport' => $sport,
            ]);

        } else if ($format == 'json') {
            $encoders = [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);
            $normalRosters = $serializer->normalize($rosters, null, [
                AbstractNormalizer::ATTRIBUTES => [
                    'year',
                    'teamName',
                    'team' => [
                        'name',
                        'slug',
                    ],
                ]
            ]);
            $jsonContent = $serializer->serialize(
                [
                    'season' => $season,
                    'rosters' => $normalRosters,
                ],
                'json'
            );

            return JsonResponse::fromJsonString($jsonContent);
        }
    }
}
