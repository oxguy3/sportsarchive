<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\Team;

class AdminController extends AbstractController
{
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
        return $this->render('admin/home.html.twig', []);
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
        $repo = $this->getDoctrine()->getRepository(Team::class);
        $teams = $repo->findNonSvg();

        return $this->render('admin/nonSvgTeamList.html.twig', [
            'teams' => $teams,
        ]);
    }
}
