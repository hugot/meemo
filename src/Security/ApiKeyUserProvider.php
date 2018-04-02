<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Security;

use App\Entity\User;
use App\Repository\ApiKeyRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User as SymfonyUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyUserProvider implements UserProviderInterface
{
    /* @var ApiKeyRepository */
    private $key_repository;

    public function __construct(
        ApiKeyRepository $key_repository,
        UserRepository   $user_repository
    ) {
        $this->key_repository  = $key_repository;
        $this->user_repository = $user_repository;
    }
    
    public function getUsernameForApiKey($key): ?string
    {
        if (($key = $this->key_repository->findOneBy([ 'key' => $key ])) !== null) {
            return $key->getUser()->getUsername();
        }
        
        return null;
    }

    public function loadUserByUsername($username)
    {
        if (($user = $this->user_repository->findOneByUsername($username)) !== null) {
            if (($key = $this->key_repository->findOneBy([ 'user' => $user ])) !== null
                && $key->getLastActive() > (new \DateTime('12 hours ago'))
            ) {
                return new SymfonyUser(
                    $user->getUsername(),
                    null,
                    [ 'ROLE_API' ]
                );
            }
        }

        throw new UsernameNotFoundException(
            sprintf(
                'There is no user by the name of "%s" with an active API key.',
                $username
            )
        );
    }

    public function refreshUser(UserInterface $user)
    {
        // Since no data is kept in the session, the user does not need to be refreshed.
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return SymfonyUser::class == $class;
    }
}
