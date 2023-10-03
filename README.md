TUNDRA
======

Tundra is a PHP-based command-line client for Mastodon.

> NOTE: License is currently set to AGPL-3.0-or-later, but this may change in
>       future commits. (Other candidates: GPL3, MIT/BSD, feedback welcome)

## Usage

```
tundra mastodon:timeline --limit 2
```

Limit isn't actually working yet. Coming soon.

Almost nothing is working, in fact, except for fetching the latest 20 public posts.

## Installation

(Pretty much none of this is working yet.)

```
git clone git@github.com:beryllium/tundra.git
cd tundra
composer install
bin/tundra configure
bin/tundra mastodon:auth
```

The configure command will create a `.env` file.

Right now, the .env file should look like:

```
MASTODON_SERVER=your.mastodon.instance
```

## Authentication

TBD
