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
        $counts = [
            'team' => $this->getDoctrine()
                ->getRepository(Team::class)
                ->count([]),
            'document' => $this->getDoctrine()
                ->getRepository(Document::class)
                ->count([]),
            'roster' => $this->getDoctrine()
                ->getRepository(Roster::class)
                ->count([]),
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
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->createQueryBuilder('t')
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
        $documents = $this->getDoctrine()
            ->getRepository(Document::class)
            ->createQueryBuilder('d')
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
        $rosters = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->createQueryBuilder('r')
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
        $seasons = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findYears();

        return $this->render('sitemap/seasons.xml.twig', [
            'seasons' => $seasons,
        ]);
    }
}
