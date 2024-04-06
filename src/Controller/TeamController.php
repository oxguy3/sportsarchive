<?php
namespace App\Controller;

use App\Entity\Document;
use App\Entity\Roster;
use App\Entity\Team;
use App\Entity\TeamLeague;
use App\Entity\TeamName;
use App\Form\DeleteType;
use App\Form\TeamType;
use App\Form\TeamLeagueType;
use App\Form\TeamNameType;
use App\Service\SportInfoProvider;
use Doctrine\Common\Collections\Criteria;
use League\Flysystem\Filesystem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\Persistence\ManagerRegistry;

class TeamController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine) {}

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
        $repo = $this->doctrine->getRepository(Team::class);
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
            $entityManager = $this->doctrine->getManager();
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
        $repo = $this->doctrine->getRepository(Team::class);
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
                    $renameSuccess = $logosFilesystem->move(
                        $oldSlug . '.' . $oldFileType,
                        $team->getSlug() . '.' . $team->getLogoFileType()
                    );
                    // TODO show an error message if it fails
                }
            }

            // persist team to db
            $entityManager = $this->doctrine->getManager();
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
        $teamRepo = $this->doctrine->getRepository(Team::class);
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
            $rosterRepo = $this->doctrine->getRepository(Roster::class);
            $rosters = $rosterRepo->findByTeam($team);

            /** @var DocumentRepository */
            $docRepo = $this->doctrine->getRepository(Document::class);
            $documents = $docRepo->findByTeam($team);

            /** @var TeamNameRepository */
            $teamNameRepo = $this->doctrine->getRepository(TeamName::class);
            $teamNames = $teamNameRepo->findByTeam($team);

            /** @var TeamLeagueRepository */
            $teamLeagueRepo = $this->doctrine->getRepository(TeamLeague::class);
            $leagues = $teamLeagueRepo->findByTeam($team);
            $leagueTeams = $teamLeagueRepo->findByLeague($team);

            return $this->render('team/teamShow.html.twig', [
                'team' => $team,
                'childTeams' => $childTeams,
                'rosters' => $rosters,
                'documents' => $documents,
                'teamNames' => $teamNames,
                'leagues' => $leagues,
                'leagueTeams' => $leagueTeams,
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

    /**
     * @Route(
     *      "/{type}/{slug}/teams.{_format}",
     *      name="team_show_members",
     *      format="html",
     *      requirements={"type"="(teams|orgs)", "_format": "html|json"}
     * )
     */
    public function showTeamMembers(Request $request, string $type, string $slug): Response
    {
        /** @var TeamRepository */
        $teamRepo = $this->doctrine->getRepository(Team::class);
        $team = $teamRepo->findBySlug($slug);
        
        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $format = $request->getRequestFormat();
        if ($team->getType() != $type) {
            return $this->redirectToRoute('team_show_members', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
                '_format' => $format,
            ]);
        }

        if ($format == 'html') {
            $childTeams = $teamRepo->findByParentTeam($team);

            /** @var TeamNameRepository */
            $teamNameRepo = $this->doctrine->getRepository(TeamName::class);
            $teamNames = $teamNameRepo->findByTeam($team);

            /** @var TeamLeagueRepository */
            $teamLeagueRepo = $this->doctrine->getRepository(TeamLeague::class);
            $leagues = $teamLeagueRepo->findByTeam($team);
            $teamLeagues = $teamLeagueRepo->findByLeague($team);

            /**
             * Iterate through the TeamLeagues and create a new array ($leagueTeams). In this new array, each object
             * represents one Team (not one TeamLeague), and the seasons from each TeamLeague will be combined into the
             * 'seasons' sub-array. This means, for example, instead of displaying "Cleveland Browns (1950 – 1995)"
             * and "Cleveland Browns (1999 – )" as two separate entries on the NFL teams list, we can just show
             * "Cleveland Browns (1950 – 1995, 1999 – )" as one entry.
             * */
            $leagueTeams = [];
            foreach ($teamLeagues as &$tl) {
                $teamId = $tl->getTeam()->getId();
                if (!array_key_exists($teamId, $leagueTeams)) {
                    $leagueTeams[$teamId] = [
                        'team' => $tl->getTeam(),
                        'seasons' => [
                            [ $tl->getFirstSeason(), $tl->getLastSeason() ]
                        ],
                        'hasSeasons' => ($tl->getFirstSeason() || $tl->getLastSeason()),
                    ];
                } else {
                    $leagueTeams[$teamId]['seasons'][] = [ $tl->getFirstSeason(), $tl->getLastSeason() ];
                    $leagueTeams[$teamId]['hasSeasons'] = $leagueTeams[$teamId]['hasSeasons'] || ($tl->getFirstSeason() || $tl->getLastSeason());
                }
            }

            // count how many leagueTeams are current vs former, and tag each leagueTeam as such
            $countCurrentLTs = 0;
            $countFormerLTs = 0;
            foreach ($leagueTeams as &$lt) {
                $isCurrent = false;
                foreach ($lt['seasons'] as $lts) {
                    $isCurrent = $isCurrent || !($lts[1]);
                    if ($isCurrent) break;
                }
                $lt['isCurrent'] = $isCurrent;
                if ($isCurrent) {
                    $countCurrentLTs++;
                } else {
                    $countFormerLTs++;
                }
            }

            return $this->render('team/teamShowMembers.html.twig', [
                'team' => $team,
                'childTeams' => $childTeams,
                'teamNames' => $teamNames,
                'leagues' => $leagues,
                'leagueTeams' => $leagueTeams,
                'countCurrentLTs' => $countCurrentLTs,
                'countFormerLTs' => $countFormerLTs,
            ]);

        } else if ($format == 'json') {
            /*$encoders = [new JsonEncoder()];
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
            );*/

            return JsonResponse::fromJsonString("not yet implemented"/*$jsonContent*/);
        }
    }

    /**
     * @Route("/teams/{slug}/add-name", name="team_name_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createTeamName(Request $request, string $slug): Response
    {
        /** @var TeamRepository */
        $repo = $this->doctrine->getRepository(Team::class);
        $team = $repo->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $teamName = new TeamName();
        $teamName->setTeam($team);
        $form = $this->createForm(TeamNameType::class, $teamName);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $teamName = $form->getData();

            // persist team to db
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($teamName);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('team/teamNameNew.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    /**
     * @Route(
     *      "/team-names/{id}/edit",
     *      name="team_name_edit",
     *      requirements={"id"="\d+"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function editTeamName(Request $request, int $id): Response
    {
        $teamName = $this->doctrine
            ->getRepository(TeamName::class)
            ->find($id);

        if (!$teamName) {
            throw $this->createNotFoundException('No team name found for id '.$id);
        }

        $team = $teamName->getTeam();

        $form = $this->createForm(TeamNameType::class, $teamName);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $teamName = $form->getData();

            // persist team to db
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($teamName);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('team/teamNameEdit.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    /**
     * @Route(
     *      "/team-names/{id}/delete",
     *      name="team_name_delete",
     *      requirements={"id"="\d+"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteTeamName(Request $request, int $id): Response
    {
        $teamName = $this->doctrine
            ->getRepository(TeamName::class)
            ->find($id);

        if (!$teamName) {
            throw $this->createNotFoundException('No team name found for id '.$id);
        }

        $team = $teamName->getTeam();

        $form = $this->createForm(DeleteType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // remove document from db
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($teamName);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('team/teamNameDelete.html.twig', [
            'teamName' => $teamName,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/teams/{slug}/add-league", name="team_league_create")
     * @IsGranted("ROLE_ADMIN")
     */
    public function createTeamLeague(Request $request, string $slug): Response
    {
        /** @var TeamRepository */
        $repo = $this->doctrine->getRepository(Team::class);
        $team = $repo->findBySlug($slug);

        if (!$team) {
            throw $this->createNotFoundException('No team found for slug '.$slug);
        }

        $teamLeague = new TeamLeague();
        $teamLeague->setTeam($team);
        $form = $this->createForm(TeamLeagueType::class, $teamLeague);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $teamLeague = $form->getData();

            // persist team to db
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($teamLeague);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('team/teamLeagueNew.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    /**
     * @Route(
     *      "/team-leagues/{id}/edit",
     *      name="team_league_edit",
     *      requirements={"id"="\d+"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function editTeamLeague(Request $request, int $id): Response
    {
        $teamLeague = $this->doctrine
            ->getRepository(TeamLeague::class)
            ->find($id);

        if (!$teamLeague) {
            throw $this->createNotFoundException('No team name found for id '.$id);
        }

        $team = $teamLeague->getTeam();

        $form = $this->createForm(TeamLeagueType::class, $teamLeague);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $teamLeague = $form->getData();

            // persist team to db
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($teamLeague);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('team/teamLeagueEdit.html.twig', [
            'form' => $form->createView(),
            'team' => $team,
        ]);
    }

    /**
     * @Route(
     *      "/team-leagues/{id}/delete",
     *      name="team_league_delete",
     *      requirements={"id"="\d+"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteTeamLeague(Request $request, int $id): Response
    {
        $teamLeague = $this->doctrine
            ->getRepository(TeamLeague::class)
            ->find($id);

        if (!$teamLeague) {
            throw $this->createNotFoundException('No team name found for id '.$id);
        }

        $team = $teamLeague->getTeam();

        $form = $this->createForm(DeleteType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // remove document from db
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($teamLeague);
            $entityManager->flush();

            return $this->redirectToRoute('team_show', [
                'type' => $team->getType(),
                'slug' => $team->getSlug(),
            ]);
        }

        return $this->render('team/teamLeagueDelete.html.twig', [
            'teamLeague' => $teamLeague,
            'form' => $form->createView(),
        ]);
    }
}
