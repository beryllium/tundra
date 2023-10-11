<?php

use League\Container\Container;
use League\Container\ContainerAwareInterface;
use League\Container\ReflectionContainer;
use Whateverthing\Tundra\Api\Mastodon\MastodonClient;

$container = new Container();
$container->delegate(new ReflectionContainer());
$container->defaultToShared();

$container
    ->inflector(ContainerAwareInterface::class)
    ->invokeMethod('setContainer', [$container]);

$container->add(MastodonClient::class, function () {
    return new MastodonClient(
        server: getenv(MastodonClient::CONFIG_SERVER),
        username: getenv(MastodonClient::CONFIG_USERNAME),
        appId: getenv(MastodonClient::CONFIG_APP_ID),
        clientId: getenv(MastodonClient::CONFIG_CLIENT_ID),
        clientSecret: getenv(MastodonClient::CONFIG_CLIENT_SECRET),
        clientAccessToken: getenv(MastodonClient::CONFIG_CLIENT_ACCESS_TOKEN),
        userAccessToken: getenv(MastodonClient::CONFIG_USER_TOKEN)
    );
});
return $container;