<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Enum;

/**
 * Supported types of exernal content.
 * @see App\Entity\ExternalContent
 */
class ContentType
{
    /* @var string */
    public const IMAGE = 'image';

    /* @var string */
    public const UNKNOWN = 'unknown';

    /* @var string[] */
    public const ALL = [
        self::IMAGE,
        self::UNKNOWN
    ];
}
