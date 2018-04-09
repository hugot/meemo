<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Security;

use App\Entity\ApiKey;

class ApiKeyExpirationChecker
{
    private const EXPIRY_TIME_STRING = '12 hours ago';

    private $expiry_time;

    public function __construct()
    {
        $this->expiry_time = new \DateTime(self::EXPIRY_TIME_STRING);
    }

    public function keyIsExpired(ApiKey $key): bool
    {
        return $key->getLastActive() < $this->expiry_time;
    }
}
