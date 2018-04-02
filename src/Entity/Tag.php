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
class Tag
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
     * @ORM\ManyToMany(targetEntity="Thing")
     * @AG\Generate(get="public", add="public")
     * @var Thing[]
     */
    private $things;

    public function __construct(string $name, array $things)
    {
        $this->setName($name);

        foreach ($things as $thing) {
            $this->addThing($thing);
        }
    }

    public function getUsage(): int
    {
        return count($this->getThings());
    }
}
