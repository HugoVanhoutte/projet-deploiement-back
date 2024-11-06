<?php

namespace App\Controller;

use App\Entity\Ads;
use App\Entity\User;
use App\Repository\AdsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
    #[Route('/api/ads/delete/{adsId}/{userId}', name: 'app_ads_delete')]
    public function delete(int $adsId, int $userId, AdsRepository $adsRepository, EntityManagerInterface $entityManager): Response
    {
        $result = $adsRepository->deleteByIdUser($adsId, $userId);
        if($result){
            $entityManager->remove($entityManager->getRepository(Ads::class)->find($adsId));
            $entityManager->flush();
            return new Response(
                json_encode(["message" => "Ad deleted successfully"]),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        }else{
            return new JsonResponse(
                ['errors' => "ads non connu"],
                Response::HTTP_BAD_REQUEST
            );
        }     
    }

    #[Route('/api/ads/verified/{adsId}', name: 'app_ads_admin_changeVerfied')]
    //#[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function changeIsVerified(int $adsId, AdsRepository $adsRepository): Response
    {
        $result = $adsRepository->isVerified($adsId);
        if($result>0){
            return new Response(
                json_encode(["message" => "Ad  state changed successfully"]),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json']
            );
        }else{
            return new JsonResponse(
                ['errors' => "ads non connu"],
                Response::HTTP_BAD_REQUEST
            );
        }     
    }
}
