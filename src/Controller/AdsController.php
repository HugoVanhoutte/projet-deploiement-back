<?php

namespace App\Controller;

use App\Entity\Ads;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdsController extends AbstractController
{
    #[Route('/api/ads/create', name: 'app_ads_create')]
    public function index( EntityManagerInterface $entityManager, ValidatorInterface $validator, Request $request): Response
{

    $data = $request->getContent();
    // Traite les données (par exemple, décoder le JSON si nécessaire)
    $jsonData = json_decode($data, true);
    $ad = new Ads();
    $ad->setTitle($jsonData['title']);
    $ad->setDescription($jsonData['description']);
    $ad->setPrice($jsonData['price']);
    $ad->setZipCode($jsonData['zipCode']);
    $ad->setWidth($jsonData['width']);
    $ad->setLength($jsonData['length']);

    $ad->setHeight($jsonData['height']);
    $ad->setImages($jsonData['images']);

    $user = $entityManager->getRepository(User::class)->find($jsonData['userId'] ?? null);
    if (!$user) {
        return new JsonResponse(
            ['error' => 'User not found'],
            Response::HTTP_BAD_REQUEST
        );
    }
    $ad->setUser($user);
    $ad->setVerified(0);
    $errors = $validator->validate($ad);

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
    $entityManager->persist($ad);
    $entityManager->flush();
    return new Response(
        json_encode(["message" => "Ad created successfully"]),
        Response::HTTP_CREATED,
        ['Content-Type' => 'application/json']
    );
}
}
