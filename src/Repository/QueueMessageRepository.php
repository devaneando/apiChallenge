<?php

namespace App\Repository;

use App\Entity\QueueMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QueueMessage>
 */
class QueueMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QueueMessage::class);
    }

    public function fetchQueue(): array
    {
        return $this->createQueryBuilder('q')
        ->where('q.status = :status')
        ->setParameter('status', QueueMessage::STATUS_PENDING)
        ->orderBy('q.lastTry', 'DESC')
        ->getQuery()
        ->getResult();
    }
}
