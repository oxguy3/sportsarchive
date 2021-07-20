<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Flysystem\Filesystem;
use App\Entity\Team;
use App\Entity\Roster;
use App\Entity\Document;
use App\Form\TeamType;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;

class TeamController extends AbstractController
{
    /**
     * @Route("/{type}.json", name="team_list", requirements={"type"="(teams|orgs)"})
     */
    public function listTeams(string $type): Response
    {
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findAllAlphabetical($type);

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $normalTeam = $serializer->normalize($teams, null, [
            AbstractNormalizer::ATTRIBUTES => [
                'name',
                'slug',
                'type',
                'logoFileType',
                'website',
                'country',
                'startYear',
                'endYear',
                'gender',
                'sport',
            ]
        ]);
        $jsonContent = $serializer->serialize(
            [
                'teams' => $normalTeam,
            ],
            'json'
        );

        return JsonResponse::fromJsonString($jsonContent);
    }

    /**
     * @Route("/{type}", name="team_list_json", requirements={"type"="(teams|orgs)"})
     */
    public function listTeamsJson(string $type): Response
    {
        $teams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findAllAlphabetical($type);

        return $this->render('team/teamList.html.twig', [
            'type' => $type,
            'teams' => $teams
        ]);
    }

    /**
     * @Route("/new-team", name="team_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createTeam(Request $request, Filesystem $logosFilesystem): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $team = $form->getData();

            /** @var UploadedFile $logoFile */
            $logoFile = $form->get('logo')->getData();

            if ($logoFile) {
                $fileExt = $logoFile->guessExtension();

                // upload the file with flysystem
                try {
                    $stream = fopen($logoFile->getRealPath(), 'r+');
                    $logosFilesystem->writeStream(
                        $team->getSlug() . '.' . $fileExt, $stream
                    );
                    fclose($stream);
                } catch (FilesystemException | UnableToWriteFile $exception) {
                    // TODO handle the error
                    throw $exception;
                }

                $team->setLogoFileType($fileExt);
            }

            // persist team to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('team/teamNew.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/teams/{slug}/edit", name="team_edit")
     */
    public function editTeam(Request $request, string $slug, Filesystem $logosFilesystem): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $form = $this->createForm(TeamType::class, $team);

        $oldSlug = $team->getSlug();
        $oldFileType = $team->getLogoFileType();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $team = $form->getData();

            /** @var UploadedFile $logoFile */
            $logoFile = $form->get('logo')->getData();

            if ($logoFile) {
                if ($oldFileType != null) {
                    // delete the old file
                    $deleteSuccess = $logosFilesystem->delete(
                        $oldSlug . '.' . $oldFileType
                    );
                    // TODO show an error message if it fails
                }
                $fileExt = $logoFile->guessExtension();

                // upload the file with flysystem
                try {
                    $stream = fopen($logoFile->getRealPath(), 'r+');
                    $logosFilesystem->writeStream(
                        $team->getSlug() . '.' . $fileExt, $stream
                    );
                    fclose($stream);
                } catch (FilesystemException | UnableToWriteFile $exception) {
                    // TODO handle the error
                    throw $exception;
                }

                $team->setLogoFileType($fileExt);

            } else if ($oldSlug != $team->getSlug()) {
                if ($oldFileType != null) {
                    $renameSuccess = $logosFilesystem->rename(
                        $oldSlug . '.' . $oldFileType,
                        $team->getSlug() . '.' . $team->getLogoFileType()
                    );
                    // TODO show an error message if it fails
                }
            }

            // persist team to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('team/teamEdit.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{type}/{slug}.json", name="team_show_json", requirements={"type"="(teams|orgs)"})
     */
    public function showTeamJson(string $type, string $slug): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }
        if ($team->getType() != $type) {
            return $this->redirectToRoute('team_show_json', [
                'type' => $team->getType(),
                'slug' => $team->getSlug()
            ]);
        }

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $normalTeam = $serializer->normalize($team, null, [
            AbstractNormalizer::ATTRIBUTES => [
                'name',
                'slug',
                'type',
                'logoFileType',
                'website',
                'country',
                'startYear',
                'endYear',
                'gender',
                'sport',
                'parentTeam' => [
                    'slug',
                    'name',
                ],
                'documents' => [
                    'fileId',
                    'filename',
                    'title',
                    'category',
                ],
                'rosters' => [
                    'year',
                ],
            ]
        ]);
        foreach ($normalTeam['rosters'] as&$roster) {
            $roster = $roster['year'];
        }
        $jsonContent = $serializer->serialize(
            [
                'team' => $normalTeam,
            ],
            'json'
        );

        return JsonResponse::fromJsonString($jsonContent);
    }

    /**
     * @Route("/{type}/{slug}", name="team_show", requirements={"type"="(teams|orgs)"})
     */
    public function showTeam(string $type, string $slug): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }
        if ($team->getType() != $type) {
            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug()
            ]);
        }

        $childTeams = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findByParentTeam($team);

        $rosters = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findByTeam($team);

        $documents = $this->getDoctrine()
            ->getRepository(Document::class)
            ->findByTeam($team);

        return $this->render('team/teamShow.html.twig', [
            'team' => $team,
            'childTeams' => $childTeams,
            'rosters' => $rosters,
            'documents' => $documents,
            'documentUrlInfix' => $_ENV['S3_DOCUMENTS_BUCKET'].'/'.$_ENV['S3_PREFIX'],
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
     * @Route("/seasons.json", name="season_list_json")
     */
    public function listSeasonsJson(): Response
    {
        $years = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findYears();

        foreach ($years as &$year) {
            $year = $year['year'];
        }

        return $this->json(['seasons' => $years]);
    }

    /**
     * @Route("/seasons/{year}", name="season_show", requirements={"year"="[\d-]+"})
     */
    public function showSeason(string $year): Response
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
