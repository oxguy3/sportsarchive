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

        $results = $qb
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
            ->getResult()
        ;

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
        // if one of the alternate names is a dead match for the query, move it to the top
        $moveToTop = [];
        $objCount = count($objects);
        for ($i = 0; $i < $objCount; ++$i) {
            $obj = $objects[$i];
            for ($j = 0; $j < count($obj->names); ++$j) {
                $tn = $obj->names[$j];
                if (strcmp($this->normalizeName($tn->getName()), $this->normalizeName($query)) === 0) {
                    $this->moveToTop($obj->names, $j);
                    $moveToTop[] = $obj;
                    unset($objects[$i]);
                    break;
                }
            }
        }
        array_unshift($objects, ...$moveToTop);

        return $objects;
    }

    private function normalizeName(string $name): string
    {
        $transliterator = \Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', \Transliterator::FORWARD);
        $name = $transliterator->transliterate($name);
        $name = strtolower($name);

        return $name;
    }

    /**
     * @param array<mixed> $array
     */
    private function moveToTop(array &$array, mixed $key): void
    {
        $temp = $array[$key];
        unset($array[$key]);
        array_unshift($array, $temp);
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
