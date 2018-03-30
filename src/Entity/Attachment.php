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
class Attachment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @AG\Generate(get="public", set="none")
     * @var int
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Thing", inversedBy="attachments")
     * @AG\Generate(get="public", set="private")
     * @var Thing
     */
    private $thing;

    /**
     * @ORM\Column(type="string")
     * @AG\Generate(get="public", set="private")
     * @var string
     */
    private $filename;

    /**
     * @ORM\Column(type="string")
     * @AG\Generate(get="public", set="private")
     * @var string
     */
    private $identifier;

    public function __construct(
        Thing $thing,
        string $filename,
        string $identifier
    ) {
        $this->setThing($thing);
        $this->setFilename($filename);
        $this->setIdentifier($identifier);
    }
}
