<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Security;

use App\Entity\ApiKey;
use App\Repository\ApiKeyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /* @var ApiKeyRepository */
    private $key_repository;

    /* @var EntityManagerInterface */
    private $entity_manager;

    public function __construct(ApiKeyRepository $key_repository, EntityManagerInterface $entity_manager)
    {
        $this->key_repository = $key_repository;
        $this->entity_manager = $entity_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(Request $request, $provider_key): ?PreAuthenticatedToken
    {
        $key = $request->query->get(ApiKey::API_KEY_PARAM);

        if (empty($key)) {
            throw new AuthenticationException('Missing token');
        }

        return new PreAuthenticatedToken('anon.', $key, $provider_key);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $provider_key)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $provider_key;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticateToken(
        TokenInterface $token,
        UserProviderInterface $user_provider,
        $provider_key
    ) {
        if (!$user_provider instanceof ApiKeyUserProvider) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The UserProviderInterface must be an instance of ApiKeyUserProvider, "%s" given.',
                    get_class($user_provider)
                )
            );
        }

        $key      = $token->getCredentials();
        $username = $user_provider->getUsernameForApiKey($key);

        if (!$username) {
            // Note: The message of this exception will be visible to the user.
            throw new CustomUserMessageAuthenticationException(
                'missing token'
            );
        }
        
        $user    = $user_provider->loadUserByUsername($username);
        $api_key = $this->key_repository->findOneBy([ 'key' => $key ]);
        $api_key->setLastActive(new \DateTime('now'));
        $this->entity_manager->persist($api_key);
        $this->entity_manager->flush();

        return new PreAuthenticatedToken($user, $key, $provider_key, $user->getRoles());
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(
            [
                'status' => 'Unauthorized',
                'message' => $exception->getMessage()
            ],
            401
        );
    }
}
