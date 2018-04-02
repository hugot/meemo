<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class UserRepository extends ServiceEntityRepository
{
    public function findOneByUsername(string $username): ?User
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT u FROM App:User u
                WHERE u.username = :username'
            )
            ->setParameter('username', $username)
            ->getOneOrNullResult();
    }
}
