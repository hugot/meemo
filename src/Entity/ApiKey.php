<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\AccessorGenerator\Annotation as AG;

/**
 * @ORM\Entity
 */
class ApiKey implements \JsonSerializable
{
    use Generated\ApiKeyMethodsTrait;

    /**
     * Name of the parameter that should represent the key in HTTP payloads.
     */
    public const API_KEY_PARAM = 'token';

    /**
     * @ORM\Id
     * @ORM\Column(name="api_key", type="string", length=100)
     * @AG\Generate(get="public", set="private")
     * @var string
     */
    private $key;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_username", referencedColumnName="username")
     * @AG\Generate(get="public", set="private")
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     * @AG\Generate(get="public", set="public")
     * @var \DateTime
     */
    private $last_active;

    public function __construct(string $key, User $user, \DateTime $last_active = null)
    {
        $this->setKey($key);
        $this->setUser($user);
        $this->setLastActive($last_active ?? new \DateTime('now'));
    }

    public function jsonSerialize(): array
    {
        return [
            self:: API_KEY_PARAM => $this->getKey(),
            'user'               => $this->getUser()
        ];
    }
}
