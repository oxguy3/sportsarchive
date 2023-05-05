<?php

namespace App\Repository;

use App\Entity\TeamName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeamName|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamName|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamName[]    findAll()
 * @method TeamName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamName::class);
    }

    // /**
    //  * @return Team[] Returns an array of Team objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findAllAlphabetical()
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC');

        return $qb->getQuery()
            ->getResult()
        ;
    }

    public function findBySlug($slug): ?TeamName
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return TeamName[] Returns an array of TeamName objects
     */
    public function findByTeam($team)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.team = :team')
            ->setParameter('team', $team)
            ->addOrderBy('t.startYear', 'ASC')
            ->addOrderBy('t.endYear', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TeamName[] Returns an array of TeamName objects
     */
    public function searchByName($query, $limit)
    {
        $query = str_replace(' ', '%', $query);
        return $this->createQueryBuilder('t')
            ->andWhere('UNACCENT(LOWER(t.name)) LIKE UNACCENT(LOWER(:query))')
            ->setParameter('query', "%${query}%")
            ->orderBy('t.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
