<?php

namespace App\Repository;

use App\Entity\Roster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Roster|null find($id, $lockMode = null, $lockVersion = null)
 * @method Roster|null findOneBy(array $criteria, array $orderBy = null)
 * @method Roster[]    findAll()
 * @method Roster[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RosterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Roster::class);
    }

    /**
     * @return Roster[]
     */
    public function findByTeam($team)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.team = :team')
            ->setParameter('team', $team)
            ->orderBy('r.year', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Roster
     */
    public function findOneByTeamYear($team, $year): ?Roster
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.team = :team')
            ->andWhere('r.year = :year')
            ->setParameter('team', $team)
            ->setParameter('year', $year)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
