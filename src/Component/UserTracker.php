<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Component;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use Symfony\Component\HttpFoundation\Request;

class UserTracker
{
    /* @var ApiKeyRepository */
    private $key_repository;

    public function __construct(ApiKeyRepository $key_repository)
    {
        $this->key_repository = $key_repository;
    }

    public function findUserForRequest(Request $request)
    {
        return $this->key_repository->findOneByKey(
            $request->query->get(ApiKey::API_KEY_PARAM)
        )
        ->getUser();
    }
}
