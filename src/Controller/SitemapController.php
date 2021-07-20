<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Team;
use App\Entity\Document;
use App\Entity\Roster;

class SitemapController extends AbstractController
{
    /**
     * @Route("/sitemap/index.xml", name="sitemap_index")
     */
    public function index(Request $request): Response
    {
        $response = $this->render('sitemap/index.xml.twig', []);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }

    /**
     * @Route("/sitemap/misc.xml", name="sitemap_misc")
     */
    public function misc(Request $request): Response
    {
        $response = $this->render('sitemap/misc.xml.twig', []);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }

    /**
     * @Route("/sitemap/teams.xml", name="sitemap_team")
     */
    public function teams(Request $request): Response
    {
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findAll();

        $response = $this->render('sitemap/teams.xml.twig', [
            'teams' => $teams,
        ]);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }

    /**
     * @Route("/sitemap/documents.xml", name="sitemap_document")
     */
    public function documents(Request $request): Response
    {
        $documents = $this->getDoctrine()
            ->getRepository(Document::class)
            ->findAll();

        $response = $this->render('sitemap/documents.xml.twig', [
            'documents' => $documents,
        ]);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }

    /**
     * @Route("/sitemap/rosters.xml", name="sitemap_roster")
     */
    public function rosters(Request $request): Response
    {
        $rosters = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findAll();

        $response = $this->render('sitemap/rosters.xml.twig', [
            'rosters' => $rosters,
        ]);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }

    /**
     * @Route("/sitemap/seasons.xml", name="sitemap_season")
     */
    public function seasons(Request $request): Response
    {
        $seasons = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findYears();

        $response = $this->render('sitemap/seasons.xml.twig', [
            'seasons' => $seasons,
        ]);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }
}
