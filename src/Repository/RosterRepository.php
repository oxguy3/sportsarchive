<?php

namespace App\Repository;

use App\Entity\Roster;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Roster>
 *
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
    public function findByTeam(Team $team)
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
    public function findByYear(string $year)
    {
        return $this->createQueryBuilder('r')
            ->join('r.team', 't', 'WITH', 'r.team = t.id')
            ->andWhere('r.year = :year')
            ->setParameter('year', $year)
            ->orderBy('r.teamName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Roster[]
     */
    public function findByYearForSport(string $year, ?string $sport)
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.team', 't', 'WITH', 'r.team = t.id')
            ->andWhere('r.year = :year');
        if ($sport !== null) {
            $qb = $qb->andWhere('t.sport = :sport')
                ->setParameter('sport', $sport);
        } else {
            $qb = $qb->andWhere('t.sport is NULL');
        }

        return $qb->setParameter('year', $year)
            ->orderBy('r.teamName', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneByTeamYear(Team $team, string $year): ?Roster
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
     *
     * @return array<array{'year': string, 'count': int}>
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
     * Returns a list of seasons for which rosters exist for teams of each sport
     *
     * @return array<array{'year': string, 'sport': string}>
     */
    public function findYearsForAllSports()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT DISTINCT roster.year, team.sport
                FROM roster
                JOIN team ON roster.team_id = team.id
                GROUP BY roster.year, team.sport
                ORDER BY team.sport, roster.year;';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    /**
     * Returns a list of seasons for which rosters exist for teams of a given sport
     * Also includes the count of rosters for each season
     *
     * @return array<array{'year': string, 'count': int}>
     */
    public function findYearsForSport(string $sport)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT DISTINCT roster.year, COUNT(roster.id) AS count
                FROM roster
                JOIN team ON roster.team_id = team.id
                WHERE team.sport = ?
                GROUP BY roster.year
                ORDER BY roster.year;';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $sport);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    /**
     * Returns a list of seasons for which rosters exist for teams that don't have a sport
     * Also includes the count of rosters for each season
     *
     * @return array<array{'year': string, 'count': int}>
     */
    public function findYearsForNoSport(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT DISTINCT roster.year, COUNT(roster.id) AS count
                FROM roster
                JOIN team ON roster.team_id = team.id
                WHERE team.sport is NULL
                GROUP BY roster.year
                ORDER BY roster.year;';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    /**
     * Returns how many rosters there are for each sport
     * (note: will not return names of sports that have 0 rosters)
     *
     * @return array<array{'count': int, 'sport': string}>
     */
    public function findSportCounts(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT DISTINCT COUNT(roster.id) AS count, team.sport
                FROM roster
                JOIN team ON roster.team_id = team.id
                GROUP BY team.sport
                ORDER BY team.sport;';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }
}
