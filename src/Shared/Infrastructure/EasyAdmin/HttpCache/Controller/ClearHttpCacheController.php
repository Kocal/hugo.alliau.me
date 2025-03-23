<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EasyAdmin\HttpCache\Controller;

use App\Shared\Domain\HttpCache\Exception\UnableToClearHttpCacheException;
use App\Shared\Domain\HttpCache\HttpCache;
use App\Shared\Infrastructure\EasyAdmin\Controller\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClearHttpCacheController extends AbstractController
{
    public function __construct(
        private readonly HttpCache $httpCache,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/admin/http-cache-clear', name: 'admin_http_cache_clear')]
    public function __invoke(): Response
    {
        try {
            $this->httpCache->clearAll();
            $this->addFlash('success', 'HTTP cache has been cleared.');
        } catch (UnableToClearHttpCacheException $unableToClearHttpCacheException) {
            $this->addFlash('danger', $unableToClearHttpCacheException->getMessage());
            $this->logger->error('Unable to clear HTTP cache.', [
                'exception' => $unableToClearHttpCacheException,
            ]);
        }

        return $this->redirect($this->adminUrlGenerator->setController(DashboardController::class)->generateUrl());
    }
}
