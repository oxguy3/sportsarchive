<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Roster;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;

class SeasonController extends AbstractController
{
    /**
     * @Route(
     *      "/seasons.{_format}",
     *      name="season_list",
     *      format="html",
     *      requirements={"_format": "html|json"}
     * )
     */
    public function listSeasons(Request $request): Response
    {
        $seasons = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findYears();

        foreach ($seasons as &$season) {
            $season = $season['year'];
        }

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('season/seasonList.html.twig', [
                'seasons' => $seasons
            ]);

        } else if ($format == 'json') {
            return $this->json(['seasons' => $seasons]);
        }
    }

    /**
     * @Route(
     *      "/seasons/{season}.{_format}",
     *      name="season_show",
     *      format="html",
     *      requirements={"season"="[\d-]+", "_format": "html|json"}
     * )
     */
    public function showSeason(Request $request, string $season): Response
    {
        $rosters = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findByYear($season);

        if (!$rosters) {
            throw $this->createNotFoundException('No rosters found for season '.$season);
        }

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('season/seasonShow.html.twig', [
                'rosters' => $rosters,
                'season' => $season,
            ]);

        } else if ($format == 'json') {
            $encoders = [new JsonEncoder()];
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers, $encoders);
            $normalRosters = $serializer->normalize($rosters, null, [
                AbstractNormalizer::ATTRIBUTES => [
                    'id',
                    'teamName',
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
