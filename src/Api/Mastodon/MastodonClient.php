<?php

namespace Whateverthing\Tundra\Api\Mastodon;

class MastodonClient
{
    public const APP_NAME = 'Tundra';
    public const APP_URL = 'https://whateverthing.com/tundra/';

    public const CONFIG_USERNAME = 'MASTODON_USERNAME';
    public const CONFIG_SERVER = 'MASTODON_SERVER';
    public const CONFIG_APP_ID = 'MASTODON_APP_ID';
    public const CONFIG_CLIENT_ID = 'MASTODON_CLIENT_ID';
    public const CONFIG_CLIENT_SECRET = 'MASTODON_CLIENT_SECRET';
    public const CONFIG_CLIENT_ACCESS_TOKEN = 'MASTODON_CLIENT_ACCESS_TOKEN';
    public const CONFIG_USER_TOKEN = 'MASTODON_USER_TOKEN';

    public function __construct(
        public string $server,
        public ?string $username = null,
        public ?string $appId = null,
        public ?string $clientId = null,
        #[\SensitiveParameter]
        public ?string $clientSecret = null,
        #[\SensitiveParameter]
        public ?string $clientAccessToken = null,
        #[\SensitiveParameter]
        public ?string $userAccessToken = null,
    ) {
    }

    public function verifyCredentials(): bool
    {
        if (!isset($this->clientAccessToken)) {
            return false;
        }
        $result = $this->request(
            apiMethod: 'apps/verify_credentials'
        );

        return self::APP_NAME === $result['name'];
    }

    public function request(...$args): mixed
    {
        $url = 'https://' . ($args['server'] ?? $this->server);
        if ($args['path'] ?? null) {
            $url .= $args['path'];
        } else {
            $url .= '/api/v1/' . ($args['apiMethod'] ?? '');
        }

        unset($args['server'], $args['apiMethod'], $args['path']);

        $context = $this->buildContextFromArgs($args);

        unset($args['httpMethod'], $args['postData']);

        $args = array_filter($args);
        if (count($args) > 0) {
            $url = $url . '?' . http_build_query($args);
        }

        $result = file_get_contents($url, context: $context);

        return json_decode($result, true);
    }

    private function buildContextFromArgs(array $args): mixed
    {
        $httpMethod = strtoupper($args['httpMethod'] ?? 'GET');

        $context = [
            'http' => [
                'method' => $httpMethod
            ]
        ];

        if ($this->clientAccessToken) {
            $context['http']['header'] = 'Authorization: Bearer ' . $this->clientAccessToken . "\r\n";
        }

        if ('POST' === $httpMethod) {
            $context['http']['header'] = ($context['http']['header'] ?? '') . 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
            $context['http']['content'] = http_build_query($args['postData'] ?? []);
        }

        return stream_context_create($context);
    }
}