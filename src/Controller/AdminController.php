<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Document;
use App\Entity\Team;
use App\Repository\DocumentRepository;

class AdminController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine) {}

    /**
     * @Route(
     *      "/admin",
     *      name="admin_home",
     *      format="html",
     *      requirements={"_format": "html"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function home(Request $request): Response
    {
        /** @var DocumentRepository */
        $docRepo = $this->doctrine->getRepository(Document::class);
        $docCategoryCounts = $docRepo->listCountsByCategory();
        $docSportCounts = $docRepo->listCountsBySport();

        return $this->render('admin/adminHome.html.twig', [
            'jsData' => [
                'docCategoryCounts' => $docCategoryCounts,
                'docSportCounts' => $docSportCounts,
            ],
        ]);
    }

    /**
     * @Route(
     *      "/admin/readerifier",
     *      name="admin_readerifier",
     *      format="html",
     *      requirements={"_format": "html"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function readerifier(Request $request): Response
    {
        // get count of tasks
        /** @var Doctrine\ORM\EntityManagerInterface */
        $entityManager = $this->doctrine->getManager();
        $conn = $entityManager->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(id) FROM messenger_messages;");
        $resultSet = $stmt->executeQuery();
        $messageCount = $resultSet->fetchOne();

        /** @var DocumentRepository */
        $docRepo = $this->doctrine->getRepository(Document::class);
        $nonReaderifiedPdfs = $docRepo->findNonReaderifiedPdfs();

        return $this->render('admin/adminReaderifier.html.twig', [
            'messageCount' => $messageCount,
            'nonReaderifiedPdfs' => $nonReaderifiedPdfs,
        ]);
    }

    /**
     * @Route(
     *      "/admin/nonsvg",
     *      name="admin_nonsvg_teams",
     *      format="html",
     *      requirements={"_format": "html"}
     * )
     * @IsGranted("ROLE_ADMIN")
     */
    public function listNonSvgTeams(Request $request): Response
    {
        /** @var TeamRepository */
        $repo = $this->doctrine->getRepository(Team::class);
        $teams = $repo->findNonSvg();

        return $this->render('admin/adminNonSvgTeams.html.twig', [
            'teams' => $teams,
        ]);
    }
}
