<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Headshot;
use App\Entity\Document;
use App\Entity\Team;
use Doctrine\Common\Collections\Criteria;
use App\Service\SportInfoProvider;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main_home")
     */
    public function home(): Response
    {
        return $this->render('main/home.html.twig', [
            'stats' => $this->getStats(),
        ]);
    }

    private function getStats(): array
    {
        $headshotCount = $this->getDoctrine()
            ->getRepository(Headshot::class)
            ->count([]);

        $documentCount = $this->getDoctrine()
            ->getRepository(Document::class)
            ->count([]);

        $teamCount = $this->getDoctrine()
            ->getRepository(Team::class)
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('type', 'teams'))
            )
            ->count([]);

        $orgCount = $this->getDoctrine()
            ->getRepository(Team::class)
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('type', 'orgs'))
            )
            ->count([]);

        return [
            'headshotCount' => $headshotCount,
            'documentCount' => $documentCount,
            'teamCount' => $teamCount,
            'orgCount' => $orgCount,
        ];
    }

    /**
     * @Route("/stats.json", name="main_stats_json")
     */
    public function statsJson(): Response
    {
        return $this->json([
            'stats' => $this->getStats(),
        ]);
    }

    private $about = [
        'founded' => 2021,
        'creator' => [
            'name' => 'Hayden Schiff',
            'website' => 'https://www.schiff.io/',
        ],
        'email' => 'haydenschiff@gmail.com',
        'twitter' => 'SportsArchive0',
        'facebook' => 'SportsArchive0',
        'sourceCode' => 'https://github.com/oxguy3/sportsarchive',
        'todoList' => 'https://trello.com/b/JnNBZ8V6/sportsarchive',
        'donate' => 'http://paypal.me/haydenschiff',
    ];

    /**
     * @Route("/about", name="main_about")
     */
    public function about(): Response
    {
        return $this->render('main/about.html.twig', [
            'about' => $this->about,
        ]);
    }

    /**
     * An easter egg for anyone who takes "add .json to any URL" too literally
     *
     * @Route("/about.json", name="main_about_json")
     */
    public function aboutJson(): Response
    {
        return $this->json(['about' => $this->about]);
    }

    /**
     * @Route("/sports.json", name="main_sports_json")
     */
    public function sportsJson(SportInfoProvider $sportInfo): Response
    {
        return $this->json(['sports' => $sportInfo->data]);
    }
}
