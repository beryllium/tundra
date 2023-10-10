<?php

declare(strict_types=1);

namespace Whateverthing\Tundra\Api\Mastodon\Entities;

/**
 * Application entity returned during authentication
 *
 * @see https://docs.joinmastodon.org/entities/Application/
 */
class Application
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $website,
        public readonly string $redirect_uri,
        public readonly string $vapid_key,
        public readonly ?string $client_id = null,
        #[\SensitiveParameter]
        public readonly ?string $client_secret = null,
        public readonly ?string $error = null,
        ...$leftovers
    ) {
        if ($leftovers) {
            throw new \Exception('Leftovers! Unexpected extra information was returned in the response.' . print_r($leftovers, true));
        }
    }
}
