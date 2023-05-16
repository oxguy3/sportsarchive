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
     * @return Roster[]
     */
    public function findByYearForSport($team, $sport)
    {
        return $this->createQueryBuilder('r')
            ->join('r.team', 't', 'WITH', 'r.team = t.id')
            ->andWhere('r.year = :year')
            ->andWhere('t.sport = :sport')
            ->setParameter('year', $team)
            ->setParameter('sport', $sport)
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

    /**
     * Returns a list of seasons for which rosters exist
     * Also includes the count of rosters for each season
     */
    public function findYears()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT DISTINCT roster.year, COUNT(id) AS count FROM roster GROUP BY roster.year ORDER BY roster.year;';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }

    /**
     * Returns a list of seasons for which rosters exist for teams of a given sport
     * Also includes the count of rosters for each season
     */
    public function findYearsForSport($sport)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT DISTINCT roster.year, COUNT(roster.id) AS count
                FROM roster
                JOIN team ON roster.team_id = team.id
                WHERE team.sport = ?
                GROUP BY roster.year
                ORDER BY roster.year;";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $sport);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }

    /**
     * Returns how many rosters there are for each sport
     * (note: will not return names of sports that have 0 rosters)
     */
    public function findSportCounts()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT DISTINCT COUNT(roster.id) AS count, team.sport
                FROM roster
                JOIN team ON roster.team_id = team.id
                GROUP BY team.sport
                ORDER BY team.sport;";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        return $resultSet->fetchAllAssociative();
    }



}
