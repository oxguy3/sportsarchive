<?php

namespace App\Repository;

use App\Entity\Headshot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Headshot|null find($id, $lockMode = null, $lockVersion = null)
 * @method Headshot|null findOneBy(array $criteria, array $orderBy = null)
 * @method Headshot[]    findAll()
 * @method Headshot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HeadshotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Headshot::class);
    }

    /**
     * @return Headshot[] Returns an array of Headshot objects
     */
    public function findByRoster($roster)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.roster = :roster')
            ->setParameter('roster', $roster)
            ->orderBy('r.personName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Headshot
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
