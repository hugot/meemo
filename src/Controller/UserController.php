<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class UserController
{
    private $user_repository;

    public function __construct(UserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }

    /**
     * @Route("/users")
     */
    public function getAllUsersAction()
    {
        return new JsonResponse([ 'users' => $this->user_repository->findAll() ]);
    }

    /**
     * @Route("/users/{username}")
     */
    public function getUserBynameAction(string $username)
    {
        $user = $this->user_repository->findOneByUsername($username);

        if ($user === null) {
            return new JsonResponse(
                [
                    'status'  => '404 Not found',
                    'message' => 'The requested user could not be found.'
                ],
                404
            );
        }

        return new JsonResponse($user);
    }
}
