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
class Tag implements \JsonSerializable
{
    use Generated\TagMethodsTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @AG\Generate(get="public", set="none")
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
     * @ORM\ManyToMany(targetEntity="Thing", mappedBy="tags")
     * @AG\Generate(get="public", add="public")
     * @var Thing[]
     */
    private $things;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tags")
     * @ORM\JoinColumn(name="user_username", referencedColumnName="username")
     * @AG\Generate(add="public", get="public")
     */
    private $user;

    public function __construct(string $name, array $things, User $user)
    {
        $this->setName($name);
        $this->setUser($user);

        foreach ($things as $thing) {
            $this->addThing($thing);
        }
    }

    public function getUsage(): int
    {
        return count($this->getThings());
    }

    public function jsonSerialize()
    {
        return [
            'name'  => $this->getName(),
            'usage' => $this->getUsage()
        ];
    }
}
