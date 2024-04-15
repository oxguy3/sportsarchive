<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\TeamName;
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
     * @return array<object{'team': Team, 'name': TeamName|null}>
     */
    public function searchByName(string $query, int $limit): array
    {
        $query = str_replace(' ', '%', $query);

        $qb = $this->getEntityManager()->createQueryBuilder();

        $query = $qb
            ->select('t, n')
            ->from(Team::class, 't')
            ->leftJoin(TeamName::class, 'n', 'WITH', $qb->expr()->andX(
                'n.team = t.id',
                'UNACCENT(LOWER(n.name)) LIKE UNACCENT(LOWER(:query))'
            ))
            ->where($qb->expr()->orX(
                'UNACCENT(LOWER(n.name)) LIKE UNACCENT(LOWER(:query))',
                'UNACCENT(LOWER(t.name)) LIKE UNACCENT(LOWER(:query))'
            ))
            ->setParameter('query', "%{$query}%")
            ->addOrderBy('t.name', 'ASC')
            ->addOrderBy('t.id', 'ASC')
            ->addOrderBy('n.type', 'DESC')
            ->addOrderBy('n.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
        ;
        $results = $query->getResult();

        /**
         * $results has all Teams and TeamNames (and nulls) in a big flat array.
         * This loop creates a new array of objects associating the Teams with
         * all the corresponding TeamNames. $results is in order such that there
         * will appear a Team, then all of its TeamNames (or null if it has none),
         * then the next Team, and so on; we take advantage of this in order to
         * build $objects in O(n) instead of O(n^2).
         */
        $objects = [];
        $obj = new \stdClass();
        for ($i = 0; $i < count($results); ++$i) {
            $entity = $results[$i];
            if ($entity instanceof Team) {
                if (property_exists($obj, 'team')) {
                    $objects[] = $obj;
                }
                $obj = (object) [
                    'team' => $entity,
                    'names' => [],
                ];
            } elseif ($entity instanceof TeamName) {
                $obj->names[] = $entity;
            }
        }
        if (property_exists($obj, 'team')) {
            $objects[] = $obj;
        }

        return $objects;
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
