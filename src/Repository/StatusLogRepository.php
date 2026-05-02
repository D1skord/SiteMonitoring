<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\StatusLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StatusLog>
 *
 * @method StatusLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatusLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatusLog[]    findAll()
 * @method StatusLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatusLog::class);
    }

    public function save(StatusLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StatusLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return StatusLog[]
     */
    public function findBySiteSince(Site $site, \DateTimeInterface $since): array
    {
        return $this->createQueryBuilder('statusLog')
            ->andWhere('statusLog.site = :site')
            ->andWhere('statusLog.timestamp >= :since')
            ->setParameter('site', $site)
            ->setParameter('since', $since)
            ->orderBy('statusLog.timestamp', 'ASC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return StatusLog[] Returns an array of StatusLog objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StatusLog
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
