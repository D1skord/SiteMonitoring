<?php

namespace App\Repository;

use App\Entity\PaymentInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentInfo>
 *
 * @method PaymentInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentInfo[]    findAll()
 * @method PaymentInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentInfo::class);
    }

    public function save(PaymentInfo $entity, bool $flush = false): PaymentInfo
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
        return $entity;
    }

    public function remove(PaymentInfo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
