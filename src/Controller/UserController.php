<?php

namespace App\Controller;

use App\Entity\User;
use ApiPlatform\Metadata\Post;
use App\Repository\AdsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MediaObjectRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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

#[Route('/api/user/admin/delete/{id}', name: 'app_user_admin_delete', methods: ['DELETE'])]
#[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
public function deleteByAdmin(
    int $id,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    MediaObjectRepository $mediaObjectRepository,
    AdsRepository $adsRepository
) {
    $user = $userRepository->find($id);

    if ($user) {
        // Récupérer toutes les annonces de l'utilisateur
        $ads = $adsRepository->findBy(['user' => $id]);
        foreach ($ads as $ad) {
            $mediaObjects = $mediaObjectRepository->findBy(['ads' => $ad->getId()]);
            foreach ($mediaObjects as $mediaObject) {
                $entityManager->remove($mediaObject);
            }
            $entityManager->remove($ad);
        }
        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(
            ["message" => "User and related ads deleted successfully"],
            Response::HTTP_OK
        );
    } else {
        return new JsonResponse(
            ['errors' => "User not found"],
            Response::HTTP_BAD_REQUEST
        );
    }
}
}
