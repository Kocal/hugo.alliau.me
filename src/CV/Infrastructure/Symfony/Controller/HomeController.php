<?php

declare(strict_types=1);

namespace App\CV\Infrastructure\Symfony\Controller;

use App\CV\Domain\Repository\ProfessionalExperienceRepository;
use App\CV\Domain\Repository\ProjectRepository;
use App\CV\Domain\Route as RouteCv;
use App\Shared\Domain\HttpCache\CacheMethodsTrait;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route('/cv', name: RouteCv::INDEX->value, options: [
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

        $response = new Response();
        $response->setEtag(self::computeEtag('cv', $latestProfessionalExperience, $latestProject));
        $response->setLastModified(max([
            $latestProfessionalExperience?->getUpdatedAt(),
            $latestProject?->getUpdatedAt(),
            $clock->now()->modify('first day of this month'),
        ]));
        $response->setMaxAge(60 * 60 * 24 * 30);
        $response->setPublic();

        if ($response->isNotModified($request)) {
            return $response;
        }

        $professionalExperiences = $professionalExperienceRepository->findAll();
        $projects = $projectRepository->findAll();

        return $this->render('cv/home.html.twig', [
            'professional_experiences' => $professionalExperiences,
            'projects' => $projects,
            'skills' => [
                'Backend' => ['PHP', 'Symfony', 'PHPUnit', 'PHPStan', 'Symfony CLI', 'Docker Compose', 'CI & CD', 'DevOps'],
                'Frontend' => ['JavaScript', 'TypeScript', 'Symfony UX', 'Vue', 'Stimulus', 'Webpack Encore', 'Cypress', 'Playwright', 'Tailwind CSS'],
                'Performances web' => ['Core Web Vitals', 'Blackfire', 'WebPageTest'],
            ],
        ], $response);
    }
}
