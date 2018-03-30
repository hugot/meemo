<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Entity;

use App\Enum\ExternalContentType;
use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\AccessorGenerator\Annotation as AG;

/**
 * External content is represented by a url and can be any type of file.
 *
 * @ORM\Entity
 */
class ExternalContent
{
    use Generated\ExternalContentMethodsTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @AG\Generate(get="public", set="none")
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Thing", inversedBy="external_contents")
     * @var Thing
     */
    private $thing;

    /**
     * @ORM\Column
     * @AG\Generate(get="public", set="public")
     * @var string
     */
    private $url;

    /**
     * Supported filetypes are in the {@see App\Enum\ExternalContentType} enum.
     * @ORM\Column
     * @var string
     */
    private $type;

    /**
     * @throws \DomainException
     */
    public function __construct(
        Thing $thing,
        string $url,
        string $type
    ) {
        $this->setThing($thing);
        $this->setUrl($url);

        if (!in_array($type, ExternalContentType::ALL)) {
            throw new \DomainException(
                sprintf('Content type "%s" is not supported.', $type)
            );
        }
    }
}
