<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EasyAdmin\Controller;

use App\Blog;
use App\CV;
use App\Places;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
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
    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addRepriseEntry('admin');
    }

    #[\Override]
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Blog');
        yield MenuItem::linkTo(Blog\Infrastructure\EasyAdmin\Controller\PostCrudController::class, 'Posts', 'fas fa-newspaper');

        yield MenuItem::section('CV');
        yield MenuItem::linkTo(CV\Infrastructure\EasyAdmin\Controller\ProfessionalExperienceCrudController::class, 'Professional experiences', 'fas fa-briefcase');
        yield MenuItem::linkTo(CV\Infrastructure\EasyAdmin\Controller\ProjectCrudController::class, 'Projects', 'fas fa-gears');

        yield MenuItem::section('Places');
        yield MenuItem::linkTo(Places\Infrastructure\EasyAdmin\Controller\PlaceCrudController::class, 'Places', 'fas fa-map-marker-alt');

        yield MenuItem::section('Tools');
        yield MenuItem::linkToUrl('Clear HTTP Cache', 'fas fa-server', '/admin/http-cache-clear');
    }
}
