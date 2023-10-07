<?php

declare(strict_types=1);

namespace Whateverthing\Tundra\Api\Mastodon\Entities;

/**
 * Account Entity returned in Posts (may differ from Account Entity returned for Profile queries)
 *
 * @see https://docs.joinmastodon.org/entities/Account/
 */
class Account
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $acct,
        public readonly string $url,
        public readonly string $display_name,
        public readonly string $note,
        public readonly string $avatar,
        public readonly string $avatar_static,
        public readonly string $header,
        public readonly string $header_static,
        public readonly bool $locked,
        public readonly array $fields,
        public readonly array $emojis,
        public readonly bool $bot,
        public readonly bool $discoverable,
        public readonly bool $group,
        public readonly string $created_at,
        public readonly int $followers_count,
        public readonly int $following_count,
        public readonly int $statuses_count,
        public readonly string $last_status_at,
        public readonly mixed $noindex = null,
        public readonly mixed $moved = null,
        public readonly mixed $suspended = null,
        public readonly mixed $limited = null,
        public readonly mixed $roles = null,
        ...$leftovers
    ) {
        if ($leftovers) {
            throw new \Exception('Leftovers! ' . print_r($leftovers, true));
        }
    }
}
