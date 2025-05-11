<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Headshot;
use App\Entity\Team;
use App\Repository\DocumentRepository;
use App\Repository\HeadshotRepository;
use App\Repository\TeamRepository;
use App\Service\SportInfoProvider;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine) {}

    #[Route(path: '/', name: 'main_home')]
    public function home(): Response
    {
        /** @var array<array{'title': non-empty-string, 'icon': non-empty-string, 'orgs': array<non-empty-string|array{0: non-empty-string, 1: non-empty-string}>}> */
        $featured = [
            [
                'title' => 'Soccer',
                'icon' => 'futbol',
                'orgs' => [
                    'major-league-soccer',
                    'usl-championship',
                    'usl-league-one',
                    'mls-next-pro',
                    ['north-american-soccer-league-1968', 'NASL (1968–1984)'],
                    ['north-american-soccer-league', 'NASL (2011–2017)'],
                    'national-womens-soccer-league',
                ],
            ],
            [
                'title' => 'Basketball',
                'icon' => 'basketball-ball',
                'orgs' => [
                    'nba',
                    'nba-g-league',
                    ['womens-national-basketball-association', 'WNBA'],
                ],
            ],
            [
                'title' => 'Baseball',
                'icon' => 'baseball-ball',
                'orgs' => [
                    'major-league-baseball',
                    'minor-league-baseball',
                    ['american-association-of-professional-baseball', 'American Association'],
                    ['atlantic-league-of-professional-baseball', 'Atlantic League'],
                    'frontier-league',
                ],
            ],
            [
                'title' => 'Football',
                'icon' => 'football-ball',
                'orgs' => [
                    'national-football-league',
                    'canadian-football-league',
                    'xfl',
                    'united-football-league',
                ],
            ],
            [
                'title' => 'Hockey',
                'icon' => 'hockey-puck',
                'orgs' => [
                    'national-hockey-league',
                    'american-hockey-league',
                    'echl',
                ],
            ],
            [
                'title' => 'Other',
                'icon' => 'ellipsis-h',
                'orgs' => [
                    ['national-collegiate-athletic-association', 'NCAA'],
                    'national-lacrosse-league',
                    'major-league-rugby',
                    'pga-tour',
                    'association-of-tennis-professionals',
                    'womens-tennis-association',
                ],
            ],
        ];

        /* pull all the org slugs out of the featured array, so we can make one big SQL query to get them all */
        $orgSlugs = [];
        foreach ($featured as $f) {
            foreach ($f['orgs'] as $o) {
                $orgSlugs[] = $o;
            }
        }

        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        $orgs = $teamRepo->createQueryBuilder('t')
            ->andWhere('t.slug IN (:slugs)')
            ->setParameter('slugs', $orgSlugs)
            ->getQuery()
            ->getResult();

        /* remake the array with the slug as the key (easier to work with in Twig) */
        $orgsAssoc = [];
        foreach ($orgs as $o) {
            $orgsAssoc[$o->getSlug()] = $o;
        }

        return $this->render('main/home.html.twig', [
            'stats' => $this->getStats(),
            'featured' => $featured,
            'orgs' => $orgsAssoc,
        ]);
    }

    /**
     * @return array{'headshotCount': int, 'documentCount': int, 'teamCount': int, 'orgCount': int}
     */
    private function getStats(): array
    {
        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        /** @var HeadshotRepository */
        $headshotRepo = $this->doctrine->getRepository(Headshot::class);
        /** @var DocumentRepository */
        $docRepo = $this->doctrine->getRepository(Document::class);

        $headshotCount = $headshotRepo->count([]);

        $documentCount = $docRepo->count([]);

        $teamCount = $teamRepo->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('type', 'teams'))
        )
            ->count();

        $orgCount = $teamRepo->matching(
            Criteria::create()
                ->andWhere(Criteria::expr()->eq('type', 'orgs'))
        )
            ->count();

        return [
            'headshotCount' => $headshotCount,
            'documentCount' => $documentCount,
            'teamCount' => $teamCount,
            'orgCount' => $orgCount,
        ];
    }

    #[Route(path: '/stats.json', name: 'main_stats_json', format: 'json')]
    public function statsJson(): Response
    {
        return $this->json([
            'stats' => $this->getStats(),
        ]);
    }

    #[Route(path: '/sports.json', name: 'main_sports_json', format: 'json')]
    public function sportsJson(SportInfoProvider $sportInfo): Response
    {
        return $this->json(['sports' => $sportInfo->data]);
    }

    #[Route(path: '/about.{_format}', name: 'main_about', format: 'html', requirements: ['_format' => 'html|json'])]
    public function about(Request $request): Response
    {
        $about = [
            'founded' => 2021,
            'creator' => [
                'name' => 'Hayden Schiff',
                'website' => 'https://www.schiff.io/',
            ],
            'email' => 'hayden@sportsarchive.net',
            'bluesky' => 'sportsarchive.net',
            'twitter' => 'SportsArchive0',
            'facebook' => 'SportsArchive0',
            'sourceCode' => 'https://github.com/oxguy3/sportsarchive',
            'trello' => [
                'code' => 'JnNBZ8V6',
                'content' => 'C1Ydfveo',
            ],
            'donate' => 'https://paypal.me/haydenschiff',
        ];

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            return $this->render('main/about.html.twig', [
                'about' => $about,
            ]);
        } elseif ($format == 'json') {
            return $this->json(['about' => $about]);
        } else {
            throw new NotAcceptableHttpException('Unknown format: '.$format);
        }
    }

    #[Route(path: '/about/api', name: 'main_about_api')]
    public function aboutApi(): Response
    {
        return $this->render('main/aboutApi.html.twig', []);
    }

    #[Route(path: '/robots.txt', name: 'main_robots_txt', format: 'txt')]
    public function robotsTxt(): Response
    {
        return $this->render('main/robots.txt.twig', []);
    }
}
