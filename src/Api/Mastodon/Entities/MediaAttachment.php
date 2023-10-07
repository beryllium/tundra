<?php

declare(strict_types=1);

namespace Whateverthing\Tundra\Api\Mastodon\Entities;

/**
 * MediaAttachment Entity returned in Posts (may differ from Account Entity returned for Profile queries)
 *
 * @see https://docs.joinmastodon.org/entities/MediaAttachment/
 */
class MediaAttachment
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $url,
        public readonly string $preview_url,
        public readonly string $remote_url,
        public readonly ?string $preview_remote_url,
        public readonly ?string $description,
        public readonly string $blurhash,
        public readonly ?array $meta,
        public readonly ?string $text_url = null, // deprecated
        ...$leftovers
    ) {
        if ($leftovers) {
            throw new \Exception('Leftovers! ' . print_r($leftovers, true));
        }
    }
}