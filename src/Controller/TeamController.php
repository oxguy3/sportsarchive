<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Team;
use App\Entity\Roster;
use App\Entity\Headshot;
use App\Form\TeamType;
use App\Form\RosterType;
use App\Form\HeadshotType;

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
            // $form->getData() holds the submitted values
            // but, the original `$team` variable has also been updated
            $team = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('team_list');
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
            throw $this->createNotFoundException(
                'No team found for slug '.$slug
            );
        }

        $rosters = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findByTeam($team);

        return $this->render('team/teamShow.html.twig', [
            'team' => $team,
            'rosters' => $rosters,
        ]);
    }

    /**
     * @Route("/teams/{slug}/new-roster", name="team_roster_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createRoster(Request $request, string $slug): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException(
                'No team found for slug '.$slug
            );
        }

        $roster = new Roster();
        $roster->setTeam($team);
        $form = $this->createForm(RosterType::class, $roster);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$team` variable has also been updated
            $roster = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($roster);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'slug' => $team->getSlug()
            ]);
        }

        return $this->render('team/rosterNew.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/teams/{slug}/{year}", name="team_roster_show")
     */
    public function showRoster(string $slug, int $year): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException(
                'No team found for slug '.$slug
            );
        }

        $roster = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findOneByTeamYear($team, $year);

        if (!$roster) {
            throw $this->createNotFoundException(
                'No roster found for year '.$year
            );
        }

        $headshots = $this->getDoctrine()
            ->getRepository(Headshot::class)
            ->findByRoster($roster);

        return $this->render('team/rosterShow.html.twig', [
            'team' => $team,
            'roster' => $roster,
            'headshots' => $headshots,
            'imageUrlPrefix' => 'https://s3.amazonaws.com/'.$_ENV['S3_HEADSHOTS_BUCKET'].'/'.$_ENV['S3_HEADSHOTS_PREFIX'],
        ]);
    }

    /**
     * @Route("/teams/{slug}/{year}/new-headshot", name="team_headshot_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createHeadshot(Request $request, string $slug, int $year): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException(
                'No team found for slug '.$slug
            );
        }

        $roster = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findOneByTeamYear($team, $year);

        if (!$roster) {
            throw $this->createNotFoundException(
                'No roster found for year '.$year
            );
        }

        $headshot = new Headshot();
        $headshot->setRoster($roster);
        $form = $this->createForm(HeadshotType::class, $headshot);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$team` variable has also been updated
            $headshot = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($headshot);
            $entityManager->flush();

            return $this->redirectToRoute('team_roster_show', [
                'slug' => $team->getSlug(),
                'year' => $roster->getYear(),
            ]);
        }

        return $this->render('team/headshotNew.html.twig', [
            'team' => $team,
            'roster' => $roster,
            'form' => $form->createView(),
        ]);
    }
}