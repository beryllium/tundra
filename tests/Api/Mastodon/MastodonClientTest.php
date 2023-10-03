<?php

namespace Whateverthing\Tundra\Api\Mastodon;

use PHPUnit\Framework\TestCase;

class MastodonClientTest extends TestCase
{
    public function testMastodonClient(): void
    {
        $client = new MastodonClient();

        self::assertInstanceOf(MastodonClient::class, $client);
    }
}