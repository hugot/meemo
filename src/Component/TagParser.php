<?php
declare(strict_types=1);

/**
 * @copyright 2018 Hugo Thunnissen
 */

namespace App\Component;

use App\Entity\Tag;
use App\Repository\TagRepository;

class TagParser
{
    private $tag_repository;

    public function __construct(TagRepository $tag_repository)
    {
        $this->tag_repository = $tag_repository;
    }

    /**
     * Parse tags from content string and return an associative array with
     * "new" and "existing" tags.
     *
     * @return mixed[string][]
     */
    public function parseNewAndExisting(string $content): array
    {
        $existing_tags = [];
        $new_tags      = [];
        $parsed_tags   = [];

        foreach ($this->extractTagsFromContent($content) as $tagname) {
            $tagname = preg_replace('/#/', '', $tagname, 1);

            if (in_array($tagname, $parsed_tags)) {
                continue;
            }

            $parsed_tags[] = $tagname;

            if (($tag = $this->tag_repository->findOneBy([ 'name' => $tagname ])) instanceof Tag) {
                $existing_tags[] = $tag;
                continue;
            }

            $new_tags[] = $tagname;
        }

        return [
            'new'      => $new_tags,
            'existing' => $existing_tags
        ];
    }

    private function extractTagsFromContent(string $content): array
    {
        preg_match_all('/#\S+/', $content, $matches);

        return array_pop($matches);
    }
}
