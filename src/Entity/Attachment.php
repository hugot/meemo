<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Entity;

use App\Enum\ContentType;
use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\AccessorGenerator\Annotation as AG;

/**
 * @ORM\Entity
 */
class Attachment implements \JsonSerializable
{
    use Generated\AttachmentMethodsTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=150)
     * @AG\Generate(get="public", set="private")
     * @var string
     */
    private $identifier;

    /**
     * @ORM\Column(type="string")
     * @AG\Generate(get="public", set="private")
     * @var string
     */
    private $content_type;

    /**
     * @ORM\ManyToOne(targetEntity="Thing", inversedBy="attachments")
     * @AG\Generate(get="public", set="public")
     * @var Thing
     */
    private $thing;

    /**
     * @ORM\Column(type="string")
     * @AG\Generate(get="public", set="private")
     * @var string
     */
    private $filename;


    public function __construct(
        Thing $thing,
        string $filename,
        string $identifier,
        string $content_type
    ) {
        $this->setThing($thing);
        $this->setFilename($filename);
        $this->setIdentifier($identifier);

        if (!in_array($content_type, ContentType::ALL)) {
            throw new \DomainException('Unsupported content type provided.');
        }
        
        $this->setContentType($content_type);
    }
    
    public function jsonSerialize()
    {
        return [
            'identifier' => $this->getIdentifier(),
            'fileName'   => $this->getFilename(),
            'type'       => $this->getContentType()
        ];
    }
}
