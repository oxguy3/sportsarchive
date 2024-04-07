<?php
namespace App\Controller;

use App\Entity\Document;
use App\Entity\Roster;
use App\Entity\Team;
use App\Repository\DocumentRepository;
use App\Repository\TeamRepository;
use App\Repository\RosterRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{

    public function __construct(private readonly ManagerRegistry $doctrine) {}

    private const PAGE_SIZE = 50000;

    #[Route(path: '/sitemap/index.xml', name: 'sitemap_index', format: 'xml')]
    public function index(Request $request): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        /** @var DocumentRepository */
        $docRepo = $this->doctrine->getRepository(Document::class);
        /** @var RosterRepository */
        $rosterRepo = $this->doctrine->getRepository(Roster::class);

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

    #[Route(path: '/sitemap/misc.xml', name: 'sitemap_misc', format: 'xml')]
    public function misc(Request $request): Response
    {
        return $this->render('sitemap/misc.xml.twig', []);
    }

    #[Route(path: '/sitemap/teams-{page}.xml', name: 'sitemap_team', format: 'xml', requirements: ['page' => '\d+'])]
    public function teams(Request $request, int $page): Response
    {
        /** @var TeamRepository */
        $repo = $this->doctrine->getRepository(Team::class);
        $teams = $repo->createQueryBuilder('t')
            ->setFirstResult($page * self::PAGE_SIZE)
            ->setMaxResults(self::PAGE_SIZE)
            ->getQuery()
            ->getResult();

        return $this->render('sitemap/teams.xml.twig', [
            'teams' => $teams,
        ]);
    }

    #[Route(path: '/sitemap/documents-{page}.xml', name: 'sitemap_document', format: 'xml', requirements: ['page' => '\d+'])]
    public function documents(Request $request, int $page): Response
    {
        /** @var DocumentRepository */
        $repo = $this->doctrine->getRepository(Document::class);
        $documents = $repo->createQueryBuilder('d')
            ->setFirstResult($page * self::PAGE_SIZE)
            ->setMaxResults(self::PAGE_SIZE)
            ->getQuery()
            ->getResult();

        return $this->render('sitemap/documents.xml.twig', [
            'documents' => $documents,
        ]);
    }

    #[Route(path: '/sitemap/rosters-{page}.xml', name: 'sitemap_roster', format: 'xml', requirements: ['page' => '\d+'])]
    public function rosters(Request $request, int $page): Response
    {
        /** @var RosterRepository */
        $repo = $this->doctrine->getRepository(Roster::class);
        $rosters = $repo->createQueryBuilder('r')
            ->setFirstResult($page * self::PAGE_SIZE)
            ->setMaxResults(self::PAGE_SIZE)
            ->getQuery()
            ->getResult();

        return $this->render('sitemap/rosters.xml.twig', [
            'rosters' => $rosters,
        ]);
    }

    #[Route(path: '/sitemap/seasons.xml', name: 'sitemap_season', format: 'xml')]
    public function seasons(Request $request): Response
    {
        /** @var RosterRepository */
        $repo = $this->doctrine->getRepository(Roster::class);
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
