<?php

namespace Whateverthing\Tundra\Api\Mastodon;

class MastodonClient
{
    public function __construct(public string $server)
    {
    }

    public function request(...$args): mixed
    {
        $url = 'https://' . ($args['server'] ?? $this->server) . '/api/v1/' . ($args['apiMethod']);

        $result = file_get_contents($url);

        return json_decode($result);
    }
}