<?php

namespace App\Controller\EasyAdmin;

use App\Entity\Document;
use App\Entity\Headshot;
use App\Entity\Roster;
use App\Entity\Team;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/easyadmin', name: 'easyadmin')]
    #[\Override]
    public function index(): Response
    {
        return $this->render('easyadmin/my_dashboard.html.twig');

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('SportsArchive Admin');
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Teams', 'fas fa-users', Team::class);
        yield MenuItem::linkToCrud('Documents', 'fas fa-file-lines', Document::class);
        yield MenuItem::linkToCrud('Rosters', 'fas fa-address-card', Roster::class);
        yield MenuItem::linkToCrud('Headshots', 'fas fa-image-portrait', Headshot::class);
        yield MenuItem::section();
        yield MenuItem::linkToRoute('Exit admin panel', 'fas fa-arrow-up-right-from-square', 'main_home');
    }
}
