<?php

namespace App\Repository;

use App\Entity\TeamLeague;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeamLeague|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamLeague|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamLeague[]    findAll()
 * @method TeamLeague[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamLeagueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamLeague::class);
    }

    // /**
    //  * @return TeamLeague[] Returns an array of TeamLeague objects
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

    /**
     * @return TeamLeague[]
     */
    public function findByTeam($team)
    {
        return $this->createQueryBuilder('tl')
            ->andWhere('tl.team = :team')
            ->setParameter('team', $team)
            ->addOrderBy('tl.firstSeason', 'ASC')
            ->addOrderBy('tl.lastSeason', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TeamLeague[]
     */
    public function findByLeague($league)
    {
        return $this->createQueryBuilder('tl')
            ->join('tl.team', 't', 'WITH', 'tl.team = t.id')
            ->andWhere('tl.league = :league')
            ->setParameter('league', $league)
            ->addOrderBy('t.name', 'ASC')
            ->addOrderBy('tl.firstSeason', 'ASC')
            ->addOrderBy('tl.lastSeason', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
