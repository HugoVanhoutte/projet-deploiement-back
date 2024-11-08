<?php

namespace App\Repository;

use App\Entity\Ads;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ads>
 */
class AdsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ads::class);
    }
    public function deleteByIdUser(int $id, int $userId)
{
    return $this->createQueryBuilder('a')
        ->where("a.id = :id")
        ->andWhere("a.user = :userId")
        ->setParameter("id", $id)
        ->setParameter("userId", $userId)
        ->getQuery()
        ->getResult();
    }
    public function isVerified($adsId)
    {
        return $this->createQueryBuilder('a')
        ->update()
        ->set('a.isVerified', '1')
        ->where("a.id = :adsId")
        ->setParameter("adsId", $adsId)
        ->getQuery()
        ->execute();
    }
    public function test($adsId){
        return $this->createQueryBuilder('a')
            ->where('a.id = :value')  // remplacez `field` par le champ que vous souhaitez filtrer
            ->setParameter('value', $adsId)
            ->getQuery()
            ->getOneOrNullResult();
    }
   public function findAllByUser():array{
    return $this->createQueryBuilder('a')
        ->select('NEW App\\DTO\\adsDTO(a.id, a.title, u.userName)')
        ->innerJoin("a.user","u")
        ->getQuery()
        ->getResult();
   }
   public function findAllByVerified():array{
    return $this->createQueryBuilder('a')
        ->select('NEW App\\DTO\\adsListingUserDTO(a.id, a.title,a.price,a.description ,u.userName)')
        ->innerJoin("a.user","u")
        ->where('a.isVerified =1')
        ->getQuery()
        ->getResult();
   }
   public function findAdsByIAdmin($id):array{
    return $this->createQueryBuilder('a')
    ->select('NEW App\\DTO\\findAdsByIAdminDTO(
    a.id, 
    a.title,
    a.price,
    a.description ,
    a.zipCode,
    a.width,
    a.length,
    a.height,
    a.isVerified,
    u.userName
    )')
    ->innerJoin("a.user","u")
    ->where("a.user = :user")
    ->setParameter("user",$id)
    ->getQuery()
    ->getResult();
   }
    //    /**
//     * @return Ads[] Returns an array of Ads objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Ads
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
