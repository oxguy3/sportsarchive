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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Service\SportInfoProvider;
use Symfony\Component\Intl\Countries;
use Doctrine\Common\Collections\Criteria;

class TeamController extends AbstractController
{
    /**
     * @Route(
     *      "/{type}.{_format}",
     *      name="team_list",
     *      format="html",
     *      requirements={"type"="(teams|orgs)", "_format": "html|json"}
     * )
     */
    public function listTeams(Request $request, string $type, SportInfoProvider $sportInfo): Response
    {
        $format = $request->getRequestFormat();
        $pageNum = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('size', $format == 'html' ? 24 : 100);
        if ($pageNum <= 0 || $pageSize <= 0) {
            throw new BadRequestHttpException('Negative pagination not allowed');
        }
        if ($pageSize > 100) {
            throw new BadRequestHttpException("Page size too big");
        }

        // retrieve and validate filters
        $sport = $request->query->get('sport', '');
        if (!in_array($sport, ['', '~']) && !$sportInfo->isSport($sport)) {
            throw new BadRequestHttpException("Unknown sport '${sport}'");
        }
        $country = $request->query->get('country', '');
        if (!in_array($country, ['', '~']) && !Countries::exists($country)) {
            throw new BadRequestHttpException("Unknown country '${country}'");
        }
        $gender = $request->query->get('gender', '');
        if (!in_array($gender, ['', 'men', 'women'])) {
            throw new BadRequestHttpException("Unknown gender '${gender}'");
        }
        $active = $request->query->get('active', '');
        if (!in_array($active, ['', 'true', 'false'])) {
            throw new BadRequestHttpException("Unknown activeness '${active}'");
        }

        // define preset filter buttons
        $presetSports = [
            'baseball',
            'basketball',
            'football',
            'hockey',
            'soccer',
            'multi-sport',
        ];
        $presetCountries = [ 'US', 'CA' ];

        /** @var TeamRepository */
        $repo = $this->getDoctrine()->getRepository(Team::class);
        $qb = $repo->createQueryBuilder('t')
            ->andWhere('t.type = :type')
            ->setParameter('type', $type);

        // add WHERE clauses based on filter params
        if ($sport != '') {
            if ($sport != '~') {
                $qb->andWhere('t.sport = :sport')
                    ->setParameter('sport', $sport);
            } else {
                $qb->andWhere('t.sport NOT IN (:presetSports)')
                    ->setParameter('presetSports', $presetSports);
            }
        }
        if ($country != '') {
            if ($country != '~') {
                $qb->andWhere('t.country = :country')
                    ->setParameter('country', $country);
            } else {
                $qb->andWhere('t.country NOT IN (:presetCountries)')
                    ->setParameter('presetCountries', $presetCountries);
            }
        }
        if ($gender != '') {
            $qb->andWhere('t.gender = :gender')
                ->setParameter('gender', $gender);
        }
        if ($active != '') {
            if ($active == 'true') {
                $qb->andWhere('t.endYear IS NULL');
            } else if ($active == 'false') {
                $qb->andWhere('t.endYear IS NOT NULL');
            }
        }

        $teams = (clone $qb)
            ->addOrderBy('t.name', 'ASC')
            ->setFirstResult(($pageNum-1)*$pageSize)
            ->setMaxResults($pageSize)
            ->getQuery()
            ->getResult();

        $countFilter = $qb->select('count(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $countAll = $repo
            ->matching(
                Criteria::create()
                    ->andWhere(Criteria::expr()->eq('type', $type))
            )
            ->count([]);

        if ($format == 'html') {
            $isRaw = $request->query->getBoolean('raw');
            $template = $isRaw ? 'team/teamList_teams.html.twig' : 'team/teamList.html.twig';
            return $this->render($template, [
                'type' => $type,
                'teams' => $teams,
                'countFilter' => $countFilter,
                'countAll' => $countAll,
                'pageNum' => $pageNum,
                'pageSize' => $pageSize,
                'presetSports' => $presetSports,
                'presetCountries' => $presetCountries,
            ]);

        } else if ($format == 'json') {
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
                    'counts' => [
                        'filter' => $countFilter,
                        'all' => $countAll,
                    ],
                    'teams' => $normalTeam,
                ],
                'json'
            );

            return JsonResponse::fromJsonString($jsonContent);
        }
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
                } catch (\Exception $exception) {
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
     * @IsGranted("ROLE_ADMIN")
     */
    public function editTeam(Request $request, string $slug, Filesystem $logosFilesystem): Response
    {
        /** @var TeamRepository */
        $repo = $this->getDoctrine()->getRepository(Team::class);
        $team = $repo->findBySlug($slug);

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
                } catch (\Exception $exception) {
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
     * @Route(
     *      "/{type}/{slug}.{_format}",
     *      name="team_show",
     *      format="html",
     *      requirements={"type"="(teams|orgs)", "_format": "html|json"}
     * )
     */
    public function showTeam(Request $request, string $type, string $slug): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->getDoctrine()->getRepository(Team::class);
        $team = $teamRepo->findBySlug($slug);
        
        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $format = $request->getRequestFormat();
        if ($team->getType() != $type) {
            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
                '_format' => $format,
            ]);
        }

        if ($format == 'html') {
            $childTeams = $teamRepo->findByParentTeam($team);

            /** @var RosterRepository */
            $rosterRepo = $this->getDoctrine()->getRepository(Roster::class);
            $rosters = $rosterRepo->findByTeam($team);

            /** @var DocumentRepository */
            $docRepo = $this->getDoctrine()->getRepository(Document::class);
            $documents = $docRepo->findByTeam($team);

            return $this->render('team/teamShow.html.twig', [
                'team' => $team,
                'childTeams' => $childTeams,
                'rosters' => $rosters,
                'documents' => $documents,
            ]);

        } else if ($format == 'json') {
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
                        'id',
                        'fileId',
                        'filename',
                        'title',
                        'category',
                        'language',
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
                [ 'team' => $normalTeam ],
                'json'
            );

            return JsonResponse::fromJsonString($jsonContent);
        }
    }
}
