<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Repository;

use App\Entity\ApiKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class ApiKeyRepository extends ServiceEntityRepository
{
    public function findOneByKey(string $key): ?ApiKey
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT k FROM App:ApiKey k
                 WHERE k.key = :key'
            )
            ->setParameter('key', $key)
            ->getOneOrNullResult();
    }
}
