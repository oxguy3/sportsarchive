<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 *
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * @return Team[]
     */
    public function findAllAlphabetical(?string $type = null): array
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC');

        if ($type != null) {
            $qb->andWhere('t.type = :type')
                ->setParameter('type', $type);
        }

        return $qb->getQuery()
            ->getResult()
        ;
    }

    public function findBySlug(string $slug): ?Team
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Team[]
     */
    public function findByParentTeam(Team $team): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.parentTeam = :parent')
            ->setParameter('parent', $team)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Team[]
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

    /**
     * @return Team[]
     */
    public function findNonSvg(): array
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC')
            ->andWhere('t.logoFileType != :logoFileType')
            ->setParameter('logoFileType', 'svg');

        return $qb->getQuery()
            ->getResult()
        ;
    }
}
