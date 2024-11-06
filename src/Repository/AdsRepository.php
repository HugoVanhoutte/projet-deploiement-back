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
