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
class User implements \JsonSerializable
{
    use Generated\UserMethodsTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=100)
     * @AG\Generate(set="public", get="public")
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=256)
     * @AG\Generate(get="public", set="public")
     * @var string
     */
    private $password;

    public function __construct(string $username, string $password)
    {
        $this->setUsername($username);
        $this->setPassword($password);
    }

    public function jsonSerialize(): array
    {
        return [
            'id'          => $this->getUsername(),
            'username'    => $this->getUsername(),
            'displayName' => ucfirst($this->getUsername())
        ];
    }
}
