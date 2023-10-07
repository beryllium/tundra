<?php

namespace Whateverthing\Tundra\Api\Mastodon;

class MastodonClient
{
    public function __construct(public string $server) {}

    public function request(...$args): mixed
    {
        $url = 'https://' . ($args['server'] ?? $this->server) . '/api/v1/' . ($args['apiMethod']);

        unset($args['server'], $args['apiMethod']);

        $args = array_filter($args);
        if (count($args) > 0) {
            $url = $url . '?' . http_build_query($args);
        }

        $result = file_get_contents($url);

        return json_decode($result, true);
    }
}