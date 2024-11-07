<?php

namespace App\Controller;

use App\Entity\User;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    
#[Route('/api/user/register', name: 'app_user_register')]
public function index( EntityManagerInterface $entityManager, ValidatorInterface $validator, Request $request,UserPasswordHasherInterface $passwordHasher): Response
{

    $data = $request->getContent();
    // Traite les données (par exemple, décoder le JSON si nécessaire)
    $jsonData = json_decode($data, true);
    $client = new User();
    $client->setEmail($jsonData['email']);
    $client->setRoles($jsonData['roles']);
    $hashedPassword = $passwordHasher->hashPassword($client, $jsonData['password']);
    $client->setPassword($hashedPassword);
    $client->setUserName($jsonData['username']);
    $client->setPhone($jsonData['phone']);
    $client->setFirstName($jsonData['firstName']);
    $client->setLastName($jsonData['lastName']);
    $errors = $validator->validate($client);

    if (count($errors) > 0) {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return new JsonResponse(
            ['errors' => $errorMessages],
            Response::HTTP_BAD_REQUEST
        );
    }
    $entityManager->persist($client);
    $entityManager->flush();
    return new Response(
        json_encode(["message" => "User registered successfully"]),
        Response::HTTP_CREATED,
        ['Content-Type' => 'application/json']
    );
}

#[Route('/api/user/update/{id}', name: 'app_user_update', methods: ['PUT'])]
public function update(
    int $id,
    EntityManagerInterface $entityManager,
    ValidatorInterface $validator,
    Request $request,
    UserPasswordHasherInterface $passwordHasher
): Response {
    // Rechercher l'utilisateur par son ID
    $client = $entityManager->getRepository(User::class)->find($id);

    // Vérifier si l'utilisateur existe
    if (!$client) {
        return new JsonResponse(
            ["message" => "User not found"],
            Response::HTTP_NOT_FOUND
        );
    }

    // Décoder les données JSON envoyées dans la requête
    $data = json_decode($request->getContent(), true);

    // Vérifier la présence des clés nécessaires dans les données JSON
    if (!$data || !isset($data['email'], 
    $data['password'], 
    $data['username'], 
    $data['phone'], 
    $data['firstName'], 
    $data['lastName'])) {
        return new JsonResponse(
            ["message" => "Invalid data provided"],
            Response::HTTP_BAD_REQUEST
        );
    }

    // Mettre à jour les données de l'utilisateur
    $client->setEmail($data['email']);
    $client->setRoles($data['roles'] ?? $client->getRoles()); // Garde les rôles existants s'ils ne sont pas fournis
    $client->setUserName($data['username']);
    $client->setPhone($data['phone']);
    $client->setFirstName($data['firstName']);
    $client->setLastName($data['lastName']);

    // Hashage du mot de passe
    $hashedPassword = $passwordHasher->hashPassword($client, $data['password']);
    $client->setPassword($hashedPassword);

    // Validation des données mises à jour
    $errors = $validator->validate($client);
    if (count($errors) > 0) {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        return new JsonResponse(
            ['errors' => $errorMessages],
            Response::HTTP_BAD_REQUEST
        );
    }

    // Persister et sauvegarder les changements
    $entityManager->flush();

    return new JsonResponse(
        ["message" => "User updated successfully"],
        Response::HTTP_OK,
        ['Content-Type' => 'application/json']
    );
}
}
