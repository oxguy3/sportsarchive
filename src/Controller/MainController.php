<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Headshot;
use App\Entity\Document;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main_home")
     */
    public function home(): Response
    {
        $headshotCount = $this->getDoctrine()
            ->getRepository(Headshot::class)
            ->count([]);

        $documentCount = $this->getDoctrine()
            ->getRepository(Document::class)
            ->count([]);

        return $this->render('main/home.html.twig', [
            'headshotCount' => $headshotCount,
            'documentCount' => $documentCount,
        ]);
    }

    /**
     * @Route("/about", name="main_about")
     */
    public function about(): Response
    {
        return $this->render('main/about.html.twig', []);
    }
}
