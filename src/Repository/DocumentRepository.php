<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 *
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
    public function findByTeam(Team $team): array
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
     * @return Document[]
     */
    public function findNonReaderifiedPdfs(): array
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

    /**
     * @return array<array{'category': string, 'count': int}>
     */
    public function listCountsByCategory(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT category, COUNT(DISTINCT(id)) AS count FROM document GROUP BY category ORDER BY count DESC;';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }

    /**
     * @return array<array{'sport': string, 'count': int}>
     */
    public function listCountsBySport(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT team.sport, COUNT(DISTINCT(document.id)) AS count FROM document JOIN team ON document.team_id = team.id GROUP BY team.sport ORDER BY count DESC;';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();

        return $resultSet->fetchAllAssociative();
    }
}
