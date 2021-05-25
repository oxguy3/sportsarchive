<?php

namespace App\Repository;

use App\Entity\RosterEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RosterEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method RosterEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method RosterEntry[]    findAll()
 * @method RosterEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RosterEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RosterEntry::class);
    }

    // /**
    //  * @return RosterEntry[] Returns an array of RosterEntry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RosterEntry
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
