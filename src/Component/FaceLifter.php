<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Component;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This class parses the content of a {@see App\Entity\Thing} and prettifies it.
 * It implements the functionality of a function called "facelift" in the original
 * meemo. I thought it was a fun joke and decided to keep it in this version.
 *
 * Kind regards, Hugo.
 *
 * @link https://github.com/nebulade/meemo/blob/master/src/logic.js Referenced file.
 */
class FaceLifter
{
    /* @var int */
    private const PRETTY_URL_LENGTH = 40;
    
    /* @var UrlGeneratorInterface */
    private $url_generator;

    public function __construct(UrlGeneratorInterface $url_generator)
    {
        $this->url_generator = $url_generator;
    }

    public function liftFace(string $content): string
    {
        return preg_replace_callback_array(
            [
                '/#\S+/' => function (array $match) {
                    return sprintf('[%1$s](#search?%1$s)', $match[0]);
                },
                '!https?://\S+!' => function (array $match) {
                    $headers    = @get_headers($match[0]);
                    $headers    = $headers === false ? [] : $headers;
                    $pretty_url = strlen($match[0]) <= self::PRETTY_URL_LENGTH
                        ? $match[0]
                        : substr($match[0], 0, 40) . '...';

                    if (0 < count(
                        array_filter($headers, function ($header) {
                            return preg_match('/Content-Type:\s*image/', $header) > 0;
                        })
                    )) {
                        return sprintf('![%s](%s)', $pretty_url, $match[0]);
                    }

                    return sprintf('[%s](%s)', $pretty_url, $match[0]);
                },
            ],
            $content
        );
    }
}
