<?php

namespace App\Repository;

use App\Entity\Headshot;
use App\Entity\Roster;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Headshot>
 *
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
     * @return Headshot[]
     */
    public function findByRoster(Roster $roster): array
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
     * @return Headshot[]
     */
    public function searchByPersonName(string $query, int $limit): array
    {
        $query = str_replace(' ', '%', $query);

        return $this->createQueryBuilder('h')
            ->andWhere('UNACCENT(LOWER(h.personName)) LIKE UNACCENT(LOWER(:query))')
            ->join('h.roster', 'r', 'WITH', 'h.roster = r.id')
            ->setParameter('query', "%{$query}%")
            ->orderBy('r.year', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
