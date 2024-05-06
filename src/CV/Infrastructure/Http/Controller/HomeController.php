<?php

namespace App\CV\Infrastructure\Http\Controller;

use App\CV\Domain\Repository\ProfessionalExperienceRepository;
use App\CV\Domain\Repository\ProjectRepository;
use App\Routing\Domain\ValueObject\RouteName;
use App\Shared\Http\Cache\CacheMethodsTrait;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    use CacheMethodsTrait;

    #[Route("/cv", name: RouteName::CV_HOME, options: [
        'sitemap' => [
            'priority' => 0.7,
            'changefreq' => UrlConcrete::CHANGEFREQ_MONTHLY,
        ],
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

        return $this->render("cv/home.html.twig", [
            'professional_experiences' => $professionalExperiences,
            'projects' => $projects,
        ], $response);
    }
}
