<?php

namespace App\Addresses\Infrastructure\Http\Controller;

use App\Routing\Domain\ValueObject\RouteName;
use App\Shared\Http\Cache\CacheMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{

    use CacheMethodsTrait;

    #[Route("/addresses", name: RouteName::ADDRESSES_HOME, options: [], methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render("address/home.html.twig");
    }
}