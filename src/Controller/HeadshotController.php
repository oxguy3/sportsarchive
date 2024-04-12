<?php

namespace App\Controller;

use App\Entity\Headshot;
use App\Entity\Roster;
use App\Entity\Team;
use App\Form\DeleteType;
use App\Form\HeadshotType;
use App\Form\RosterType;
use App\Repository\HeadshotRepository;
use App\Repository\RosterRepository;
use App\Repository\TeamRepository;
use App\Service\HeadshotPersister;
use Doctrine\Persistence\ManagerRegistry;
use League\Flysystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class HeadshotController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $doctrine) {}

    #[Route(path: '/teams/{slug}/new-roster', name: 'roster_create')]
    #[IsGranted('ROLE_ADMIN')]
    public function createRoster(Request $request, string $slug): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        $team = $teamRepo->findBySlug($slug);

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
            $entityManager = $this->doctrine->getManager();
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

    #[Route(path: '/teams/{slug}/{year}/edit-roster', name: 'roster_edit', requirements: ['year' => '[\d-]+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editRoster(Request $request, string $slug, string $year): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        $team = $teamRepo->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        /** @var RosterRepository */
        $rosterRepo = $this->doctrine->getRepository(Roster::class);
        $roster = $rosterRepo->findOneByTeamYear($team, $year);

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
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($roster);
            $entityManager->flush();

            return $this->redirectToRoute('roster_show', [
                'slug' => $team->getSlug(),
                'year' => $roster->getYear(),
            ]);
        }

        return $this->render('headshot/rosterEdit.html.twig', [
            'roster' => $roster,
            'team' => $team,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/teams/{slug}/{year}.{_format}', name: 'roster_show', format: 'html', requirements: ['year' => '[\d-]+', '_format' => 'html|json'])]
    public function showRoster(Request $request, string $slug, string $year, Security $security, HeadshotPersister $persister): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        $team = $teamRepo->findBySlug($slug);
        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        /** @var RosterRepository */
        $rosterRepo = $this->doctrine->getRepository(Roster::class);
        $roster = $rosterRepo->findOneByTeamYear($team, $year);
        if (!$roster) {
            throw $this->createNotFoundException('No roster found for year '.$year);
        }

        $headshotForm = false;

        // create headshot dropzone uploader
        if ($security->isGranted('ROLE_ADMIN')) {
            $headshot = new Headshot();
            $headshot->setRoster($roster);
            $headshotForm = $this->createForm(HeadshotType::class, $headshot, [
                'is_new' => true,
                'is_dropzone' => true,
            ]);

            $headshotForm->handleRequest($request);
            if ($headshotForm->isSubmitted() && $headshotForm->isValid()) {
                $headshot = $headshotForm->getData();

                /** @var UploadedFile|null $imageFile */
                $imageFile = $headshotForm->get('image')->getData();

                $originalFilename = (string) $imageFile->getClientOriginalName();
                $headshot = $persister->extractDetailsFromFilename($originalFilename, $headshot);

                $persister->persist($headshot, $imageFile);

                return new Response('ok');
            }
        }

        $format = $request->getRequestFormat();
        if ($format == 'html') {
            /** @var HeadshotRepository */
            $headshotRepo = $this->doctrine->getRepository(Headshot::class);
            $headshots = $headshotRepo->findByRoster($roster);

            return $this->render('headshot/rosterShow.html.twig', [
                'team' => $team,
                'roster' => $roster,
                'headshots' => $headshots,
                'headshotForm' => $headshotForm ? $headshotForm->createView() : null,
            ]);
        } elseif ($format == 'json') {
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
                ],
            ]);
            $jsonContent = $serializer->serialize(
                ['roster' => $normalTeam],
                'json'
            );

            return JsonResponse::fromJsonString($jsonContent);
        } else {
            throw new NotAcceptableHttpException('Unknown format: '.$format);
        }
    }

    /**
     * This route is deprecated; it is no longer used anywhere on the site.
     */
    #[Route(path: '/rosters/{id}', name: 'roster_show_by_id', requirements: ['id' => '[\d-]+'])]
    public function showRosterById(int $id): Response
    {
        $roster = $this->doctrine
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

    #[Route(path: '/teams/{slug}/{year}/new-headshot', name: 'headshot_create', requirements: ['year' => '[\d-]+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createHeadshot(Request $request, string $slug, string $year, HeadshotPersister $persister): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        $team = $teamRepo->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        /** @var RosterRepository */
        $rosterRepo = $this->doctrine->getRepository(Roster::class);
        $roster = $rosterRepo->findOneByTeamYear($team, $year);

        if (!$roster) {
            throw $this->createNotFoundException('No roster found for year '.$year);
        }

        $headshot = new Headshot();
        $headshot->setRoster($roster);
        $form = $this->createForm(HeadshotType::class, $headshot, [
            'is_new' => true,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $headshot = $form->getData();

            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();

            $persister->persist($headshot, $imageFile);

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

    #[Route(path: '/headshots/{id}/edit', name: 'headshot_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function editHeadshot(Request $request, int $id, HeadshotPersister $persister, Filesystem $headshotsFilesystem): Response
    {
        $oldHeadshot = $this->doctrine
            ->getRepository(Headshot::class)
            ->find($id);

        if (!$oldHeadshot) {
            throw $this->createNotFoundException('No headshot found for id '.$id);
        }

        $roster = $oldHeadshot->getRoster();
        $team = $roster->getTeam();

        $form = $this->createForm(HeadshotType::class, $oldHeadshot, [
            'is_new' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $oldFilename = $oldHeadshot->getFilename();
            $newHeadshot = $form->getData();

            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('image')->getData();

            $persister->persist($newHeadshot, $imageFile);

            // delete the old file if a new file was uploaded
            if ($imageFile) {
                try {
                    $headshotsFilesystem->delete($oldFilename);
                } catch (\Exception $exception) {
                    // TODO handle the error
                    throw $exception;
                }
            }

            return $this->redirectToRoute('roster_show', [
                'slug' => $team->getSlug(),
                'year' => $roster->getYear(),
            ]);
        }

        return $this->render('headshot/headshotEdit.html.twig', [
            'team' => $team,
            'roster' => $roster,
            'headshot' => $oldHeadshot,
            'imageUrlInfix' => $_ENV['S3_HEADSHOTS_BUCKET'].'/'.$_ENV['S3_PREFIX'],
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/headshots/{id}/delete', name: 'headshot_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteHeadshot(Request $request, int $id, Filesystem $headshotsFilesystem): Response
    {
        $headshot = $this->doctrine
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
            try {
                $headshotsFilesystem->delete($headshot->getFilename());
            } catch (\Exception $exception) {
                // TODO handle the error
                throw $exception;
            }

            // persist headshot to db
            $entityManager = $this->doctrine->getManager();
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
