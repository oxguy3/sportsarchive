<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\TeamName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamName>
 *
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

    /**
     * @return TeamName[]
     */
    public function findAllAlphabetical(): array
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC');

        return $qb->getQuery()
            ->getResult()
        ;
    }

    public function findBySlug(string $slug): ?TeamName
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return TeamName[]
     */
    public function findByTeam(Team $team): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.team = :team')
            ->setParameter('team', $team)
            ->addOrderBy('t.type', 'DESC')
            ->addOrderBy('t.firstSeason', 'ASC')
            ->addOrderBy('t.lastSeason', 'ASC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return TeamName[]
     */
    public function searchByName(string $query, int $limit): array
    {
        $query = str_replace(' ', '%', $query);

        return $this->createQueryBuilder('t')
            ->andWhere('UNACCENT(LOWER(t.name)) LIKE UNACCENT(LOWER(:query))')
            ->setParameter('query', "%{$query}%")
            ->orderBy('t.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
