<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'app_admin_login', methods: ['POST'])]
    public function login(#[CurrentUser] $user = null): Response
    {
        return $this->json($user);
        if (null === $user) {
            return $this->json([
                'message' => $user,
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier le rôle "ROLE_ADMIN" dans la liste des rôles de l'utilisateur
        // if (!in_array('ROLE_ADMIN', $user->getRoles(), true)) {
        //     return $this->json([
        //         'message' => 'Access denied. Admin role required.',
        //     ], Response::HTTP_FORBIDDEN);
        // }

        // Retourner les informations utilisateur sans générer de token
        return $this->json([
            'user' => $user->getUserIdentifier(),
            'id' => $user->getId(),
        ]);
    }
}
