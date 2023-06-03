<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * @return Document[]
     */
    public function findByTeam($team)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.team = :team')
            ->setParameter('team', $team)
            ->addOrderBy('d.category', 'ASC')
            ->addOrderBy('d.title', 'ASC')
            ->addOrderBy('d.language', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Document[] Returns an array of Document objects
     */
    public function findNonReaderifiedPdfs()
    {
        $qb = $this->createQueryBuilder('d');

        return $qb
            ->andWhere('d.filename LIKE :pattern')
            ->setParameter('pattern', '%.pdf')
            ->andWhere('d.isBookReader = false')
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Document
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
