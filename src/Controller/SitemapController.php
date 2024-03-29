<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Team;
use App\Entity\Document;
use App\Entity\Roster;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SitemapController extends AbstractController
{
    private const PAGE_SIZE = 50000;
    /**
     * @Route("/sitemap/index.xml", name="sitemap_index", format="xml")
     */
    public function index(Request $request): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->getDoctrine()->getRepository(Team::class);
        /** @var DocumentRepository */
        $docRepo = $this->getDoctrine()->getRepository(Document::class);
        /** @var RosterRepository */
        $rosterRepo = $this->getDoctrine()->getRepository(Roster::class);

        $counts = [
            'team' => $teamRepo->count([]),
            'document' => $docRepo->count([]),
            'roster' => $rosterRepo->count([]),
        ];
        foreach ($counts as &$count) {
            $count = ceil($count/self::PAGE_SIZE) - 1;
        }
        return $this->render('sitemap/index.xml.twig', [
            'counts' => $counts,
        ]);
    }

    /**
     * @Route("/sitemap/misc.xml", name="sitemap_misc", format="xml")
     */
    public function misc(Request $request): Response
    {
        return $this->render('sitemap/misc.xml.twig', []);
    }

    /**
     * @Route(
     *      "/sitemap/teams-{page}.xml",
     *      name="sitemap_team",
     *      format="xml",
     *      requirements={"page"="\d+"}
     * )
     */
    public function teams(Request $request, int $page): Response
    {
        /** @var TeamRepository */
        $repo = $this->getDoctrine()->getRepository(Team::class);
        $teams = $repo->createQueryBuilder('t')
            ->setFirstResult($page * self::PAGE_SIZE)
            ->setMaxResults(self::PAGE_SIZE)
            ->getQuery()
            ->getResult();

        return $this->render('sitemap/teams.xml.twig', [
            'teams' => $teams,
        ]);
    }

    /**
     * @Route(
     *      "/sitemap/documents-{page}.xml",
     *      name="sitemap_document",
     *      format="xml",
     *      requirements={"page"="\d+"}
     * )
     */
    public function documents(Request $request, int $page): Response
    {
        /** @var DocumentRepository */
        $repo = $this->getDoctrine()->getRepository(Document::class);
        $documents = $repo->createQueryBuilder('d')
            ->setFirstResult($page * self::PAGE_SIZE)
            ->setMaxResults(self::PAGE_SIZE)
            ->getQuery()
            ->getResult();

        return $this->render('sitemap/documents.xml.twig', [
            'documents' => $documents,
        ]);
    }

    /**
     * @Route(
     *      "/sitemap/rosters-{page}.xml",
     *      name="sitemap_roster",
     *      format="xml",
     *      requirements={"page"="\d+"}
     * )
     */
    public function rosters(Request $request, int $page): Response
    {
        /** @var RosterRepository */
        $repo = $this->getDoctrine()->getRepository(Roster::class);
        $rosters = $repo->createQueryBuilder('r')
            ->setFirstResult($page * self::PAGE_SIZE)
            ->setMaxResults(self::PAGE_SIZE)
            ->getQuery()
            ->getResult();

        return $this->render('sitemap/rosters.xml.twig', [
            'rosters' => $rosters,
        ]);
    }

    /**
     * @Route("/sitemap/seasons.xml", name="sitemap_season", format="xml")
     */
    public function seasons(Request $request): Response
    {
        /** @var RosterRepository */
        $repo = $this->getDoctrine()->getRepository(Roster::class);
        $sportCounts = $repo->findSportCounts();
        $seasonsAll = $repo->findYears();
        $seasonsSport = $repo->findYearsForAllSports();

        return $this->render('sitemap/seasons.xml.twig', [
            'sportCounts' => $sportCounts,
            'seasonsAll' => $seasonsAll,
            'seasonsSport' => $seasonsSport,
        ]);
    }
}
