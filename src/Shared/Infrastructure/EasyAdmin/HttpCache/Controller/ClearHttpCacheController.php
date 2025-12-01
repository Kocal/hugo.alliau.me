<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\EasyAdmin\HttpCache\Controller;

use App\Shared\Domain\HttpCache\Exception\UnableToClearHttpCacheException;
use App\Shared\Domain\HttpCache\HttpCache;
use App\Shared\Infrastructure\EasyAdmin\Controller\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function __invoke(Request $request): Response
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

        $previousUrl = $request->headers->has('referer') && !str_contains($request->headers->get('referer'), '/admin/http-cache-clear')
            ? $request->headers->get('referer')
            : $this->adminUrlGenerator->setController(DashboardController::class)->generateUrl();

        return $this->redirect($previousUrl);
    }
}
