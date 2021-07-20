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
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Roster[]
     */
    public function findByYear($team)
    {
        return $this->createQueryBuilder('r')
            ->join('r.team', 't', 'WITH', 'r.team = t.id')
            ->andWhere('r.year = :year')
            ->setParameter('year', $team)
            ->orderBy('r.teamName', 'ASC')
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

    public function findYears()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT DISTINCT roster.year FROM roster ORDER BY roster.year;';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

}
