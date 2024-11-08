<?php

namespace App\Controller;





use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;




use App\Entity\Ads;
use App\Entity\MediaObject;
use App\Repository\AdsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
class MediaObjectController extends  ApiTestCase
{
    #[Route('/api/upload', name: 'app_update_image', methods: ['POST'])]
    public function index(EntityManagerInterface $entityManager, 
    AdsRepository $adsRepository,
    ValidatorInterface $validator, Request $request,
    ManagerRegistry $doctrine): Response
    {
        // Récupérer l'ID de l'annonce depuis les données du formulaire
        $adsId = $request->request->get('ads');
        return new JsonResponse($adsId);
        
        if (!$adsId) {
            return new JsonResponse(['error' => 'L\'ID de l\'annonce est requis.'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer l'entité Ads depuis la base de données
        $repository = $doctrine()->getRepository(Ads::class);
        $ads = $repository->findOneBy(['id' => $adsId]);
     //   $ads = $adsRepository->findOneBy(array("id"=>$adsId))->getResult();;
        if (!$ads) {
            //throw $this->createNotFoundException('Ads not found');
        }
       
        $mediaObject = new MediaObject();
        $mediaObject->setAds($ads);
        // // Valider et enregistrer
        $errors = $validator->validate($mediaObject);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        $entityManager->persist($mediaObject);
        $entityManager->flush();
        return new JsonResponse(
            ["message" => "Image associée avec succès à l'annonce."],
            Response::HTTP_CREATED,
            ['Content-Type' => 'application/json']
        );
    }
}