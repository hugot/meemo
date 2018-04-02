<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Controller;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use App\Security\PasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class LoginController
{
    /* @var UserRepository */
    private $user_repository;

    /* @var ApiKeyRepository */
    private $key_repository;

    /* @var PasswordHasher */
    private $hasher;

    /* @var EntityManagerInterface */
    private $entity_manager;

    public function __construct(
        UserRepository          $user_repository,
        ApiKeyRepository        $key_repository,
        PasswordHasher          $hasher,
        EntityManagerInterface  $entity_manager
    ) {
        $this->user_repository = $user_repository;
        $this->key_repository  = $key_repository;
        $this->hasher          = $hasher;
        $this->entity_manager  = $entity_manager;
    }

    /**
     * @Route("/login", name="app-login", methods={"POST"})
     */
    public function loginAction(Request $request): JsonResponse
    {
        $body = $request->getContent();
        if (empty($body) || ($json = json_decode($body, true)) === null) {
            return new JsonResponse(
                [
                    'status'  => 'Bad Request',
                    'message' => 'Json body expected'
                ],
                400
            );
        }

        if (($user = $this->user_repository->findOneByUsername($json['username'])) !== null) {
            if (($key = $this->key_repository->findOneByUser($user)) !== null) {
                return new JsonResponse($key, 201);
            }

            if ($this->hasher->verify($json['password'], $user->getPassword())) {
                $key = new ApiKey(uniqid($user->getUsername(), true), $user);
                $this->entity_manager->persist($key);
                $this->entity_manager->flush();

                return new JsonResponse($key, 201);
            }
        }

        return new JsonResponse(
            [
                'status'  => 'Unauthorized',
                'message' => 'invalid credentials'
            ],
            401
        );
    }

    /**
     * @Route("/profile")
     */
    public function profileAction(Request $request)
    {
        $user = $this->key_repository->findOneByKey($request->query->get(ApiKey::API_KEY_PARAM))
            ->getUser();
        return new JsonResponse(
            [
                'mailbox' => null,
                'user'    => $user
            ],
            200
        );
    }
}
