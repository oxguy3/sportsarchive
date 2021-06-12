<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Team;
use App\Entity\Roster;
use App\Entity\Document;
use App\Form\TeamType;

class TeamController extends AbstractController
{
    /**
     * @Route("/teams", name="team_list")
     */
    public function listTeams(): Response
    {
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findAllAlphabetical();

        return $this->render('team/teamList.html.twig', ['teams' => $teams]);
    }

    /**
     * @Route("/teams/new", name="team_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createTeam(Request $request): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $team = $form->getData();

            // persist team to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('team/teamNew.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/teams/{slug}", name="team_show")
     */
    public function showTeam(string $slug): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $rosters = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findByTeam($team);

        $documents = $this->getDoctrine()
            ->getRepository(Document::class)
            ->findByTeam($team);

        return $this->render('team/teamShow.html.twig', [
            'team' => $team,
            'rosters' => $rosters,
            'documents' => $documents,
            'documentUrlInfix' => $_ENV['S3_DOCUMENTS_BUCKET'].'/'.$_ENV['S3_DOCUMENTS_PREFIX'],
        ]);
    }

    /**
     * @Route("/teams/{slug}/edit", name="team_edit")
     */
    public function editTeam(Request $request, string $slug): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $team = $form->getData();

            // persist team to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'slug' => $slug,
            ]);
        }

        return $this->render('team/teamEdit.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/seasons", name="season_list")
     */
    public function listSeasons(): Response
    {
        $years = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findYears();

        return $this->render('team/seasonList.html.twig', ['years' => $years]);
    }

    /**
     * @Route("/seasons/{year}", name="season_show", requirements={"year"="\d+"})
     */
    public function showSeason(int $year): Response
    {
        $rosters = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findByYear($year);

        if (!$rosters) {
            throw $this->createNotFoundException('No rosters found for year '.$year);
        }

        return $this->render('team/seasonShow.html.twig', [
            'rosters' => $rosters,
            'year' => $year,
        ]);
    }
}
