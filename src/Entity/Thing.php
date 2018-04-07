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
     * @ORM\Column(type="text")
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
     * @ORM\OneToMany(targetEntity="Attachment", mappedBy="thing", cascade={"persist", "remove"})
     * @AG\Generate(add="public", remove="public", get="public")
     * @var Attachment[]
     */
    private $attachments;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="things", cascade={"persist"})
     * @ORM\JoinTable(name="thing_has_tag",
     *  joinColumns={ @ORM\JoinColumn(name="thing_id", referencedColumnName="id") },
     *  inverseJoinColumns={ @ORM\JoinColumn(name="tag_name", referencedColumnName="name") }
     * )
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
     * @ORM\JoinColumn(name="user_has_thing", referencedColumnName="username")
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

    /**
     * @ORM\Column(type="text")
     * @AG\Generate(get="public", set="public")
     * @var string
     */
    private $rich_content;

    /**
     * @ORM\Column(type="boolean")
     * @AG\Generate(get="public", set="public")
     * @var bool
     */
    private $archived;

    public function __construct(
        string $content,
        string $rich_content,
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
        $this->setRichContent($rich_content);
        $this->setArchived(false);

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
            'createdAt'       => $this->getCreatedAt()->getTimestamp() * 1000,
            'modifiedAt'      => $this->getModifiedAt()->getTimestamp() * 1000,
            'tags'            => $this->getTags()->toArray(),
            'externalContent' => $this->getExternalContents()->toArray(),
            'attachments'     => $this->getAttachments()->toArray(),
            'public'          => $this->isPublic(),
            // TODO: Implement "shared" fieature
            'shared'          => false,
            'archived'        => $this->isArchived(),
            'sticky'          => $this->isSticky(),
            'richContent'     => $this->getRichContent()
        ];
    }

    public function updateModifiedAt()
    {
        $this->setModifiedAt(new \DateTime('now'));
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }
}
