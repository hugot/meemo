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
class Thing implements \JsonSerializable
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
     * @AG\Generate(add="public", remove="public", get="public")
     * @var ExternalContent[]
     */
    private $external_contents;

    /**
     * @ORM\OneToMany(targetEntity="Attachment", mappedBy="thing")
     * @AG\Generate(add="public", remove="public", get="public")
     * @var Attachment[]
     */
    private $attachments;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", mappedBy="things")
     * @AG\Generate(add="public", remove="public", get="public")
     * @var Tag[]
     */
    private $tags;

    /**
     * @ORM\Column(type="boolean")
     * @AG\Generate(set="public", is="public")
     * @var bool
     */
    private $public;

    /**
     * @ORM\Column(type="boolean")
     * @AG\Generate(set="public", is="public")
     * @var bool
     */
    private $sticky;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="things")
     * @ORM\JoinColumn(name="user_username", referencedColumnName="username")
     * @AG\Generate(set="private", get="public")
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     * @AG\Generate(set="private", get="public")
     * @var \DateTime
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime")
     * @AG\Generate(set="private", get="public")
     * @var \DateTime
     */
    private $modified_at;

    public function __construct(
        string $content,
        User   $user,
        array  $tags = [],
        array  $external_contents = [],
        array  $attachments = [],
        bool   $public = false,
        bool   $sticky = false
    ) {
        $this->setContent($content);
        $this->setPublic($public);
        $this->setUser($user);
        $this->setCreatedAt(new \DateTime('now'));
        $this->setModifiedAt(new \DateTime('now'));
        $this->setSticky($sticky);

        foreach ($tags as $tag) {
            $this->addTag($tag);
        }

        foreach ($external_contents as $ex_content) {
            $this->addExternalContent($ex_content);
        }

        foreach ($attachments as $attachment) {
            $this->addAttachment($attachment);
        }
    }

    public function jsonSerialize()
    {
        return [
            '_id'             => $this->getId(),
            'content'         => $this->getContent(),
            'createdAt'       => $this->getCreatedAt()->getTimestamp(),
            'modifiedAt'      => $this->getModifiedAt()->getTimestamp(),
            'tags'            => $this->getTags()->toArray(),
            'externalContent' => $this->getExternalContents()->toArray(),
            'attachments'     => $this->getAttachments()->toArray(),
            'public'          => $this->isPublic(),
            // TODO: Implement "shared" fieature
            'shared'          => false,
            'sticky'          => $this->isSticky(),
            'richContent'     => $this->getContent()
        ];
    }

    /**
     * @ORM\Prepersist
     * @ORM\PreUpdate
     */
    private function updateModifiedAt()
    {
        $this->setModifiedAt(new \DateTime('now'));
    }
}
