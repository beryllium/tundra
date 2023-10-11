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

    protected array $lastRequestHeaders = [];

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
        $this->lastRequestHeaders = $http_response_header ?? []; // This is some PHP magic: https://www.php.net/manual/en/reserved.variables.httpresponseheader.php

        return json_decode($result, true);
    }

    /**
     * Examines the headers of the previous request, looking for a Link header.
     *
     * Returns a processed Link array
     *
     * @return array
     */
    public function getPaginationLinks(): array
    {
        $linkHeader = null;
        foreach ($this->lastRequestHeaders as $header) {
            if (!str_starts_with($header, 'Link: ')) {
                continue;
            }

            $linkHeader = $header;
            break;
        }

        if (!$linkHeader) {
            return [];
        }

        /*
            the rules: https://docs.joinmastodon.org/api/guidelines/#pagination

            1. The links will be returned all via one Link header, separated by a comma and a space (, )
            2. Each link consists of a URL and a link relation, separated by a semicolon and a space (; )
            3. The URL will be surrounded by angle brackets (<>), and the link relation will be surrounded by double quotes ("") and prefixed with rel=.
            4. The value of the link relation will be either prev or next.

            Following the next link should show you older results. Following the prev link should show you newer results.
         */
        $matches = [];
        $result = preg_match('/Link: <([^>]*)>; rel="([a-z]*)", <([^>]*)>; rel="([a-z]*)"/', $linkHeader, $matches);

        if (!$result || count($matches) !== 5) {
            return [];
        }

        return [
            $matches[2] => $matches[1], // "next"
            $matches[4] => $matches[3], // "prev"
        ];
    }

    private function buildContextFromArgs(array $args): mixed
    {
        $httpMethod = strtoupper($args['httpMethod'] ?? 'GET');

        $context = [
            'http' => [
                'method' => $httpMethod
            ]
        ];

        if ($this->userAccessToken || $this->clientAccessToken) {
            $context['http']['header'] = 'Authorization: Bearer ' . ($this->userAccessToken ?? $this->clientAccessToken) . "\r\n";
        }

        if ('POST' === $httpMethod) {
            $context['http']['header'] = ($context['http']['header'] ?? '') . 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
            $context['http']['content'] = http_build_query($args['postData'] ?? []);
        }

        return stream_context_create($context);
    }
}