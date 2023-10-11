#!/usr/bin/env php
<?php
/*
    Tundra - Command-line client for Mastodon
    Copyright (C) 2023  Kevin Boyd <kevin@whateverthing.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use League\Container\Container;
use Symfony\Component\Console\Application;
use Whateverthing\Tundra\Commands\Mastodon\Account\BookmarksCommand;
use Whateverthing\Tundra\Commands\Mastodon\AuthCommand;
use Whateverthing\Tundra\Commands\Mastodon\Timeline\TimelineCommand;

/** @var Container $app */
$app = require __DIR__ . '/../bootstrap.php';

$console = new Application('Tundra');

// Look into CommandLoaderInterface for this
$console->add($app->get(AuthCommand::class));
$console->add($app->get(TimelineCommand::class));
$console->add($app->get(BookmarksCommand::class));

$console->run();