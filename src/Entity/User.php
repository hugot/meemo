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

    /**
     * @ORM\OneToMany(targetEntity="Setting", mappedBy="user", cascade={"persist", "remove"})
     * @var Setting[]
     */
    private $settings;

    /**
     * @ORM\OneToMany(targetEntity="Thing", mappedBy="user", cascade={"persist", "remove"})
     * @AG\Generate(add="public", remove="public", get="public")
     * @var Thing[]
     */
    private $things;

    /**
     * @ORM\OneToMany(targetEntity="Tag", mappedBy="user", cascade={"persist", "remove"})
     * @AG\Generate(add="public", remove="public", get="public")
     * @var Tag[]
     */
    private $tags;

    public function __construct(
        string $username,
        string $password,
        array $settings = [],
        array $things = [],
        array $tags = []
    ) {
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setSettings($settings);

        foreach ($things as $thing) {
            $this->addThing($thing);
        }

        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings(array $settings)
    {
        $this->settings = $settings;
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
