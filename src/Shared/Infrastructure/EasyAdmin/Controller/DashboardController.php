<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EasyAdmin\Controller;

use App\Blog;
use App\CV;
use App\Places;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    #[\Override]
    public function index(): Response
    {
        return $this->render('admin/home.html.twig');
    }

    #[\Override]
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Hugo Alliaume')
            ->setFaviconPath('images/icons/favicon.svg')
            ->generateRelativeUrls()
        ;
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Blog');
        yield MenuItem::linkToCrud('Posts', 'fas fa-newspaper', Blog\Domain\Post::class);

        yield MenuItem::section('CV');
        yield MenuItem::linkToCrud('Professional experiences', 'fas fa-briefcase', CV\Domain\ProfessionalExperience::class);
        yield MenuItem::linkToCrud('Projects', 'fas fa-gears', CV\Domain\Project::class);

        yield MenuItem::section('Places');
        yield MenuItem::linkToCrud('Places', 'fas fa-map-marker-alt', Places\Domain\Place::class);

        yield MenuItem::section('Tools');
        yield MenuItem::linkToUrl('Clear HTTP Cache', 'fas fa-server', '/admin/http-cache-clear');
    }
}
