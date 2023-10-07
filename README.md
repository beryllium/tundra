TUNDRA
======

Tundra is a PHP-based command-line client for Mastodon.

> NOTE: License is currently set to AGPL-3.0-or-later, but this may change in
>       future commits. (Other candidates: GPL3, MIT/BSD, feedback welcome)

## Usage

```
tundra mastodon:timeline --limit 2
```

Almost nothing is implemented so far, except for fetching the latest public
posts and displaying them in a table.

## Installation

Tundra requires PHP 8.1 or higher.

To get started, clone it and install dependencies:

```
git clone git@github.com:beryllium/tundra.git
cd tundra
composer install
```

At this point, we begin to get hypothetical (not yet implemented):

```
bin/tundra configure
bin/tundra mastodon:auth
```

The configure command will create a `.env` file.

Right now, the .env file should look like:

```
MASTODON_SERVER=your.mastodon.instance
```

To test out tundra in its current form, make a `.env` file like the above and
enter your own instance's domain as the MASTODON_SERVER value.

## Authentication

TBD
