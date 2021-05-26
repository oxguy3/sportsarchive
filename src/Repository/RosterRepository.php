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
    public function findByTeam($teamId)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.team = :teamId')
            ->setParameter('teamId', $value)
            ->orderBy('r.year', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Roster
     */
    public function findOneByTeamYear($teamId, $year): ?Roster
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.team = :teamId')
            ->andWhere('r.year = :year')
            ->setParameter('teamId', $teamId)
            ->setParameter('year', $year)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
