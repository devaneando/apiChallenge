<?php

namespace App\Repository;

use App\Entity\StockRequestHistory;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StockRequestHistory>
 */
class StockRequestHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockRequestHistory::class);
    }

    public function fetchAllFromUser(User $user): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.user = :user')
            ->setParameter('user', $user)
            ->orderBy('h.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
