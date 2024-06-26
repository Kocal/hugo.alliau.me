<?php

namespace App\Shared\Infrastructure\EasyAdmin\HttpCache\Controller;

use App\Admin\Infrastructure\Http\Controller\DashboardController;
use App\Shared\Domain\HttpCache\HttpCache;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClearHttpCacheController extends AbstractController
{
    public function __construct(
        private HttpCache $httpCache,
        private AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin/http-cache-clear', name: 'admin_http_cache_clear')]
    public function __invoke(): Response
    {
        $this->httpCache->clearAll();

        $this->addFlash('success', 'HTTP cache has been cleared.');

        return $this->redirect($this->adminUrlGenerator->setController(DashboardController::class)->generateUrl());
    }
}
