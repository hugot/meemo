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
class Setting
{
    use Generated\SettingMethodsTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @AG\Generate(set="none", get="public")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @AG\Generate(set="private", get="public")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     * @AG\Generate(set="public", get="public")
     * @var string
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="settings")
     * @ORM\JoinColumn(name="user_username", referencedColumnName="username")
     * @AG\Generate(set="private", get="public")
     * @var User
     */
    private $user;

    public function __construct(string $name, string $value, User $user)
    {
        $this->setName($name);
        $this->setValue($value);
        $this->setUser($user);
    }
}
