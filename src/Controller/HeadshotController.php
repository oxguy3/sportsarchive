<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Flysystem\Filesystem;
use App\Entity\Team;
use App\Entity\Roster;
use App\Entity\Headshot;
use App\Entity\Document;
use App\Form\RosterType;
use App\Form\HeadshotType;
use App\Form\DeleteType;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;

class HeadshotController extends AbstractController
{
    /**
     * @Route("/teams/{slug}/new-roster", name="roster_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createRoster(Request $request, string $slug): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $roster = new Roster();
        $roster->setTeam($team);
        $roster->setTeamName($team->getName());
        $form = $this->createForm(RosterType::class, $roster);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $roster = $form->getData();

            // persist roster to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($roster);
            $entityManager->flush();

            return $this->redirectToRoute('roster_show', [
                'slug' => $team->getSlug(),
                'year' => $roster->getYear(),
            ]);
        }

        return $this->render('headshot/rosterNew.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/teams/{slug}/{year}/edit-roster", name="roster_edit", requirements={"year"="[\d-]+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function editRoster(Request $request, string $slug, string $year): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $roster = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findOneByTeamYear($team, $year);

        if (!$roster) {
            throw $this->createNotFoundException('No roster found for year '.$year);
        }

        // fill in default name if none set
        if (!$roster->getTeamName()) {
            $roster->setTeamName($team->getName());
        }

        $form = $this->createForm(RosterType::class, $roster);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $roster = $form->getData();

            // persist roster to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($roster);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug()
            ]);
        }

        return $this->render('headshot/rosterEdit.html.twig', [
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/teams/{slug}/{year}.json", name="roster_show_json", requirements={"year"="[\d-]+"})
     */
    public function showRosterJson(string $slug, string $year): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $roster = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findOneByTeamYear($team, $year);

        if (!$roster) {
            throw $this->createNotFoundException('No roster found for year '.$year);
        }

        // $headshots = $this->getDoctrine()
        //     ->getRepository(Headshot::class)
        //     ->findByRoster($roster);

        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $normalTeam = $serializer->normalize($roster, null, [
            AbstractNormalizer::ATTRIBUTES => [
                'year',
                'teamName',
                'notes',
                'team' => [
                    'slug',
                    'name',
                ],
                'headshots' => [
                    'personName',
                    'jerseyNumber',
                    'filename',
                    'role',
                    'title',
                ],
            ]
        ]);
        $jsonContent = $serializer->serialize(
            [
                'roster' => $normalTeam,
            ],
            'json'
        );

        return JsonResponse::fromJsonString($jsonContent);
    }

    /**
     * @Route("/teams/{slug}/{year}", name="roster_show", requirements={"year"="[\d-]+"})
     */
    public function showRoster(string $slug, string $year): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $roster = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findOneByTeamYear($team, $year);

        if (!$roster) {
            throw $this->createNotFoundException('No roster found for year '.$year);
        }

        $headshots = $this->getDoctrine()
            ->getRepository(Headshot::class)
            ->findByRoster($roster);

        return $this->render('headshot/rosterShow.html.twig', [
            'team' => $team,
            'roster' => $roster,
            'headshots' => $headshots,
            'imageUrlInfix' => $_ENV['S3_HEADSHOTS_BUCKET'].'/'.$_ENV['S3_PREFIX'],
        ]);
    }

    /**
     * @Route("/rosters/{id}", name="roster_show_by_id", requirements={"id"="[\d-]+"})
     */
    public function showRosterById(int $id): Response
    {
        $roster = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->find($id);

        if (!$roster) {
            throw $this->createNotFoundException('No roster found for id '.$id);
        }

        $team = $roster->getTeam();

        return $this->redirectToRoute('roster_show', [
            'slug' => $team->getSlug(),
            'year' => $roster->getYear(),
        ]);

    }

    /**
     * @Route("/teams/{slug}/{year}/new-headshot", name="headshot_create", requirements={"year"="[\d-]+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function createHeadshot(Request $request, string $slug, string $year, Filesystem $headshotsFilesystem): Response
    {
        $team = $this->getDoctrine()
            ->getRepository(Team::class)
            ->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $roster = $this->getDoctrine()
            ->getRepository(Roster::class)
            ->findOneByTeamYear($team, $year);

        if (!$roster) {
            throw $this->createNotFoundException('No roster found for year '.$year);
        }

        $headshot = new Headshot();
        $headshot->setRoster($roster);
        $form = $this->createForm(HeadshotType::class, $headshot);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $headshot = $form->getData();

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'image' field is not required
            // so the file must be processed only when a file is uploaded
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // upload the file with flysystem
                try {
                    $stream = fopen($imageFile->getRealPath(), 'r+');
                    $headshotsFilesystem->writeStream($newFilename, $stream);
                    fclose($stream);
                } catch (FilesystemException | UnableToWriteFile $exception) {
                    // TODO handle the error
                    throw $exception;
                }

                $headshot->setFilename($newFilename);
            }

            // persist headshot to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($headshot);
            $entityManager->flush();

            return $this->redirectToRoute('roster_show', [
                'slug' => $team->getSlug(),
                'year' => $roster->getYear(),
            ]);
        }

        return $this->render('headshot/headshotNew.html.twig', [
            'team' => $team,
            'roster' => $roster,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/headshots/{id}/edit", name="headshot_edit", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function editHeadshot(Request $request, int $id, Filesystem $headshotsFilesystem): Response
    {
        $headshot = $this->getDoctrine()
            ->getRepository(Headshot::class)
            ->find($id);

        if (!$headshot) {
            throw $this->createNotFoundException('No headshot found for id '.$id);
        }

        $roster = $headshot->getRoster();
        $team = $roster->getTeam();

        $form = $this->createForm(HeadshotType::class, $headshot);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $headshot = $form->getData();

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'image' field is not required
            // so the file must be processed only when a file is uploaded
            if ($imageFile) {

                // delete the old file
                $deleteSuccess = $headshotsFilesystem->delete($headshot->getFilename());
                // TODO show an error message if it fails

                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // upload the new file with flysystem
                try {
                    $stream = fopen($imageFile->getRealPath(), 'r+');
                    $headshotsFilesystem->writeStream($newFilename, $stream);
                    fclose($stream);
                } catch (FilesystemException | UnableToWriteFile $exception) {
                    // TODO handle the error
                    throw $exception;
                }

                $headshot->setFilename($newFilename);
            }

            // persist headshot to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($headshot);
            $entityManager->flush();

            return $this->redirectToRoute('roster_show', [
                'slug' => $team->getSlug(),
                'year' => $roster->getYear(),
            ]);
        }

        return $this->render('headshot/headshotEdit.html.twig', [
            'team' => $team,
            'roster' => $roster,
            'headshot' => $headshot,
            'imageUrlInfix' => $_ENV['S3_HEADSHOTS_BUCKET'].'/'.$_ENV['S3_PREFIX'],
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/headshots/{id}/delete", name="headshot_delete", requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteHeadshot(Request $request, int $id, Filesystem $headshotsFilesystem): Response
    {
        $headshot = $this->getDoctrine()
            ->getRepository(Headshot::class)
            ->find($id);

        if (!$headshot) {
            throw $this->createNotFoundException('No headshot found for id '.$id);
        }

        $roster = $headshot->getRoster();
        $team = $roster->getTeam();

        $form = $this->createForm(DeleteType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $success = $headshotsFilesystem->delete($headshot->getFilename());
            // TODO show an error message if it fails

            // persist headshot to db
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($headshot);
            $entityManager->flush();

            return $this->redirectToRoute('roster_show', [
                'slug' => $team->getSlug(),
                'year' => $roster->getYear(),
            ]);
        }

        return $this->render('headshot/headshotDelete.html.twig', [
            'headshot' => $headshot,
            'imageUrlInfix' => $_ENV['S3_HEADSHOTS_BUCKET'].'/'.$_ENV['S3_PREFIX'],
            'form' => $form->createView(),
        ]);
    }
}
