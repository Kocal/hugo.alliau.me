<?php

namespace App\Http\Controller;

use App\Domain\Routing\ValueObject\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route("/", name: RouteName::HOME)]
    public function __invoke(): Response
    {
        return $this->render("home.html.twig");
    }
}
