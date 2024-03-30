<?php

namespace App\Http\Controller\Admin;

use App\Domain\Blog as Blog;
use App\Domain\CV as CV;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/home.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Hugo Alliaume')
            ->setFaviconPath('images/icons/favicon.svg')
            ->generateRelativeUrls()
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Blog');
        yield MenuItem::linkToCrud('Posts', 'fas fa-newspaper', Blog\Post::class);

        yield MenuItem::section('CV');
        yield MenuItem::linkToCrud('Professional experiences', 'fas fa-briefcase', CV\ProfessionalExperience::class);
        yield MenuItem::linkToCrud('Projects', 'fas fa-gears', CV\Project::class);
    }
}
