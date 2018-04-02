<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Security;

class PasswordHasher
{
    private const COST   = 10;
    private const METHOD = PASSWORD_BCRYPT;

    public function hash(string $password): string
    {
        return password_hash($password, self::METHOD, [ 'cost' => self::COST ]);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
