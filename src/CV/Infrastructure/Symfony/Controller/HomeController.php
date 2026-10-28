<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\Symfony\Controller;

use App\CV\Domain\Data\Route as RouteCv;
use App\CV\Domain\Repository\ProfessionalExperienceRepository;
use App\CV\Domain\Repository\ProjectRepository;
use App\Shared\Infrastructure\Symfony\HttpCache\CacheableResponseTrait;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheableResponseTrait;

    #[Route(path: [
        'en' => '/cv',
        'fr' => '/fr/cv',
    ], name: RouteCv::INDEX->value, options: [
        'sitemap' => true,
    ], methods: ['GET'])]
    public function __invoke(
        Request $request,
        ProfessionalExperienceRepository $professionalExperienceRepository,
        ProjectRepository $projectRepository,
        ClockInterface $clock,
    ): Response {
        $latestProfessionalExperience = $professionalExperienceRepository->findOneLatest();
        $latestProject = $projectRepository->findOneLatest();

        $response = $this->createCacheableResponse(
            etag: ['cv', $latestProfessionalExperience, $latestProject],
            lastModified: max([
                $latestProfessionalExperience?->getUpdatedAt(),
                $latestProject?->getUpdatedAt(),
                $clock->now()
                    ->modify('first day of this month'),
            ]),
            sharedMaxAge: 60 * 60 * 24 * 30,
        );

        if ($response->isNotModified($request)) {
            return $response;
        }

        $professionalExperiences = $professionalExperienceRepository->findAll();
        $projects = $projectRepository->findAllVisible();

        return $this->render('cv/home.html.twig', [
            'professional_experiences' => $professionalExperiences,
            'projects' => $projects,
            'skills' => [
                'cv.skills.backend' => ['PHP', 'Symfony', 'PHPUnit', 'PHPStan', 'Symfony CLI', 'Docker Compose', 'CI & CD', 'DevOps'],
                'cv.skills.frontend' => ['JavaScript', 'TypeScript', 'Symfony UX', 'Vue', 'Stimulus', 'Symfony Reprise', 'Webpack Encore', 'Cypress', 'Playwright', 'Tailwind CSS'],
                'cv.skills.web_perfs' => ['Core Web Vitals', 'Blackfire', 'WebPageTest'],
            ],
        ], $response);
    }
}
