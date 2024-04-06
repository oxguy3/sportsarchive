<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
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

    // /**
    //  * @return Team[] Returns an array of Team objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findAllAlphabetical($type = null)
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

    public function findBySlug($slug): ?Team
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Team[] Returns an array of Team objects
     */
    public function findByParentTeam($team)
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
     * @return Team[] Returns an array of Team objects
     */
    public function searchByName(string $query, $limit)
    {
        $query = str_replace(' ', '%', $query);
        return $this->createQueryBuilder('t')
            ->andWhere('UNACCENT(LOWER(t.name)) LIKE UNACCENT(LOWER(:query))')
            ->setParameter('query', "%${query}%")
            ->orderBy('t.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Team[] Returns an array of Team objects
     */
    public function findNonSvg()
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC')
            ->andWhere('t.logoFileType != :logoFileType')
            ->setParameter('logoFileType', "svg");

        return $qb->getQuery()
            ->getResult()
        ;
    }
}
