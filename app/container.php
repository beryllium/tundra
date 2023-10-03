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
    return new MastodonClient(server: getenv('MASTODON_SERVER'));
});
return $container;