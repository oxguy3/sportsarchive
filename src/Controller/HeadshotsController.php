<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HeadshotsController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function home(): Response
    {
        return $this->render('headshots/home.html.twig', []);
    }

    /**
     * @Route("/headshots/{number}")
     */
    public function number(int $number): Response
    {
        #$number = random_int(0, 100);

        return $this->render('headshots/number.html.twig', [
            'number' => $number,
        ]);
    }
}
