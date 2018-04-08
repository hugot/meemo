<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Component;

use App\Entity\ApiKey;
use App\Entity\Attachment;
use App\Entity\Thing;
use App\Enum\ContentType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AttachmentAdder
{
    /* @var UrlGeneratorInterface */
    private $url_generator;

    public function __construct(UrlGeneratorInterface $url_generator)
    {
        $this->url_generator = $url_generator;
    }

    public function addAttachmentsToContent(
        array $attachments,
        string $content,
        string $username,
        ?ApiKey $key = null
    ): string {
        foreach ($attachments as $attachment) {
            $content = str_replace(
                sprintf('[%s]', $attachment->getFilename()),
                sprintf(
                    $this->formatFor($attachment),
                    $attachment->getFilename(),
                    $this->url_generator->generate('app-serve-file', [
                        'username' => $username,
                        'filename' => $attachment->getIdentifier()
                    ]),
                    $key === null ? '' : sprintf('%s=%s', ApiKey::API_KEY_PARAM, $key->getKey())
                ),
                $content
            );
        }

        return $content;
    }

    public function addAttachmentsToThings(array $things, ?ApiKey $key = null): array
    {
        return array_map(function (Thing $thing) use ($key) {
            $thing->setRichContent(
                $this->addAttachmentsToContent(
                    $thing->getAttachments()->toArray(),
                    $thing->getRichContent(),
                    $thing->getUser()->getUsername(),
                    $thing->isPublic() ? null : $key
                )
            );

            return $thing;
        }, $things);
    }

    private function formatFor(Attachment $attachment): string
    {
        if ($attachment->getContentType() === ContentType::IMAGE) {
            return '![%s](%s?%s)';
        }

        return '[%s](%s?%s)';
    }
}
