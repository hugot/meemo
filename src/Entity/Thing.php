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
class Thing
{
    use Generated\ThingMethodsTrait;

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
     * @AG\Generate(get="public", set="public")
     * @var string
     */
    private $content;

    /**
     * @ORM\OneToMany(targetEntity="ExternalContent", mappedBy="thing")
     * @AG\Generate(add="public", get="public")
     * @var ExternalContent[]
     */
    private $external_contents;

    /**
     * @ORM\OneToMany(targetEntity="Attachment", mappedBy="thing")
     * @AG\Generate(add="public", get="public")
     * @var Attachment[]
     */
    private $attachments;

    /**
     * @ORM\Column(type="boolean")
     * @AG\Generate(is="public")
     * @var bool
     */
    private $public;

    public function __construct(
        string $content,
        array $external_contents = [],
        array $attachments = [],
        bool $public = false
    ) {
        $this->setContent($content);
        $this->setIsPublic($public);

        foreach ($external_contents as $ex_content) {
            $this->addExternalContent($ex_content);
        }

        foreach ($attachments as $attachment) {
            $this->addAttachment($attachment);
        }
    }
}
