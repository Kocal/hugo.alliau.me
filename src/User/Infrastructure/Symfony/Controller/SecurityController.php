<?php

namespace App\User\Infrastructure\Symfony\Controller;

use App\User\Domain\Route as RouteUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: RouteUser::LOGIN->value)]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        //         if ($this->getUser()) {
        //             return $this->redirectToRoute('target_path');
        //         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@EasyAdmin/page/login.html.twig', [
            // parameters usually defined in Symfony login forms
            'last_username' => $lastUsername,
            'error' => $error,

            // parameters defined in EasyAdmin login form
            'csrf_token_intention' => 'authenticate',
        ]);
    }

    #[Route(path: '/logout', name: RouteUser::LOGOUT->value)]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
