<?php

namespace App\Repository;

use App\Entity\ExpireDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExpireDate>
 *
 * @method ExpireDate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpireDate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpireDate[]    findAll()
 * @method ExpireDate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpireDateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpireDate::class);
    }

    public function save(ExpireDate $entity, bool $flush = false): ExpireDate
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
        return $entity;
    }

    public function remove(ExpireDate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}