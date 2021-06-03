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
        return $this->createQueryBuilder('h')
            ->andWhere('h.roster = :roster')
            ->setParameter('roster', $roster)
            ->orderBy('h.personName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Headshot[] Returns an array of Headshot objects
     */
    public function searchByPersonName($query)
    {
        $query = str_replace(' ', '%', $query);
        return $this->createQueryBuilder('h')
            ->andWhere('LOWER(h.personName) LIKE LOWER(:query)')
            ->join('h.roster', 'r', 'WITH', 'h.roster = r.id')
            ->setParameter('query', "%${query}%")
            ->orderBy('r.year', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Headshot
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
