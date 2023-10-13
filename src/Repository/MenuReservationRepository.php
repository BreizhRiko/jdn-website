<?php

namespace App\Repository;

use App\Entity\MenuReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MenuReservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuReservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuReservation[]    findAll()
 * @method MenuReservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuReservation::class);
    }

    public function countMenuReservations(): array
    {
        return $this->createQueryBuilder('mr')
            ->select('COUNT(mr)')
            ->groupBy('mr.menu')
            ->orderBy('mr.menu', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }


    // /**
    //  * @return MenuReservation[] Returns an array of MenuReservation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MenuReservation
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
