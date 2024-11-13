<?php

namespace App\Controller;

use App\Entity\Ads;
use App\Entity\User;
use App\Entity\Categories;
use App\Entity\MediaObject;
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

class AdsController extends AbstractController
{
    #[Route('/api/ads/create', name: 'app_ads_create')]
    #[IsGranted(new Expression('is_granted("ROLE_USER")'))]
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
    foreach($jsonData['categories'] as $categorie){
        $role = $entityManager->getRepository(Categories::class)->findOneBy(['id' => $categorie]);
        if ($role) {
            $ad->addIsIn($role);
        }
    }
    
    $media = new MediaObject();
    $media->setAds($ad);
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
    #[IsGranted(new Expression('is_granted("ROLE_USER")'))]
    public function delete(int $adsId,
    int $userId, 
    AdsRepository $adsRepository, 
    EntityManagerInterface $entityManager,
    MediaObjectRepository $mediaObjectRepository): Response
    {
        $ads = $adsRepository->findOneBy(['id' => $adsId, 'user' => $userId]);
        if($ads){
            foreach ($ads->getIsIn() as $categorie) {
                $ads->removeIsIn($categorie);
            }
            // Récupérer le MediaObject associé à cette annonce
            $mediaObject = $mediaObjectRepository->findOneBy(['ads' => $adsId]);
            if ($mediaObject) {
                // Supprimer le fichier associé
                $entityManager->remove($mediaObject);
            }

            $entityManager->remove($entityManager->getRepository(Ads::class)->find($adsId));
            
            $entityManager->flush();
            //sppression des images

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
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
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
    #[Route('/api/ads/{adsId}', name: 'app_ads_admin_delete')]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function deleteByAdmin(int $adsId,
    AdsRepository $adsRepository, 
    EntityManagerInterface $entityManager,
    MediaObjectRepository $mediaObjectRepository): Response
    {
        $result = $adsRepository->find($adsId);
        if($result){
            foreach ($result->getIsIn() as $categorie) {
                $result->removeIsIn($categorie);
            }
            // Récupérer le MediaObject associé à cette annonce
            $mediaObject = $mediaObjectRepository->findBy(['ads' => $adsId]);
            for($i=0;$i<count($mediaObject);$i++){
                if ($mediaObject[$i]) {
                    // Supprimer le fichier associé
                    $entityManager->remove($mediaObject[$i]);
                }
            }
            $entityManager->remove($entityManager->getRepository(Ads::class)->find($adsId));     
            $entityManager->flush();
            return new Response(
                json_encode(["message" => "Ad deleted successfully"]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }else{
            return new JsonResponse(
                ['errors' => "ads non connu"],
                Response::HTTP_BAD_REQUEST
            );
        }     
    }

    #[Route('/api/ads/admin/listing', name: 'app_ads_admin_listing')]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function listIsVerified( AdsRepository $adsRepository): Response
    {
        $result = $adsRepository->findAllByUser();

        $adsData = array_map(function ($ad) {
        return [
            "id"=>$ad->getId(),
            'title' => $ad->getTitle(),
            'userName' => $ad->getUserName(),
            
        ];
    }, $result);
        if($result>0){
            return new Response(
                json_encode(["result" => $adsData]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }else{
            return new JsonResponse(
                ['errors' => "ads non connu"],
                Response::HTTP_BAD_REQUEST
            );
        }     
    }


    #[Route('/api/ads/listing', name: 'app_ads_listing', methods :["GET"])]
    public function listAllAds( AdsRepository $adsRepository): Response
    {
        $result = $adsRepository->findAllByVerified();

        $adsData = array_map(function ($ad) {
        return [
            "id"=>$ad->getId(),
            'title' => $ad->getTitle(),
            'userName' => $ad->getUserName(),
            "price"=> $ad->getPrice(),
            "description"=>$ad->getDescription()
        ];
    }, $result);
        if($result>0){
            return new Response(
                json_encode(["result" => $adsData]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }else{
            return new JsonResponse(
                ['errors' => "ads non connu"],
                Response::HTTP_BAD_REQUEST
            );
        }     
    }


    #[Route('/api/ads/admin/detail/{id}', name: 'app_ads_admin_detail', methods :["GET"])]
    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    public function detailAdminAds(int $id, AdsRepository $adsRepository, MediaObjectRepository $media): Response
    {
        $result = $adsRepository->findAdsByIAdmin($id);
        for($i=0;$i<count($result);$i++){
            $result[$i]->image = $media->findBy(array('ads'=>$result[$i]->id));
        }      
        if($result){
            return new Response(
                json_encode(["result" => $result]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }else{
            return new JsonResponse(
                ['errors' => "ads non connu"],
                Response::HTTP_BAD_REQUEST
            );
        }     
    }

    #[Route('/api/ads/detail/{id}', name: 'app_ads_detail', methods :["GET"])]
    public function detailUserAds(int $id, AdsRepository $adsRepository, MediaObjectRepository $media): Response
    {
        $result = $adsRepository->findAdsById($id);
        for($i=0;$i<count($result);$i++){
            $result[$i]->image = $media->findBy(array('ads'=>$result[$i]->id));
        }      
        if($result){
            return new Response(
                json_encode(["result" => $result]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }else{
            return new JsonResponse(
                ['errors' => "ads non connu"],
                Response::HTTP_BAD_REQUEST
            );
        }     
    }
    #[Route('/api/ads/reporting/{adsId}/{userId}', name: 'app_ads_reporting', methods: ['GET'])]
    #[IsGranted(new Expression('is_granted("ROLE_USER")'))]
    public function reporting(
        int $adsId,
        int $userId,
        AdsRepository $adsRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['message' => "User not found"], Response::HTTP_NOT_FOUND);
        }
        $ads = $adsRepository->find($adsId);
        if (!$ads) {
            return new JsonResponse(['message' => "Ad not found"], Response::HTTP_NOT_FOUND);
        }
        if (!$ads->getReporting()->contains($user)) {
            $ads->addReporting($user);
            $message = "Ad reporting";
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => $message], Response::HTTP_OK);
    }
}
