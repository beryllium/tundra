TUNDRA
======

Tundra is a PHP-based command-line client for Mastodon.

> NOTE: License is currently set to AGPL-3.0-or-later, but this may change in
>       future commits. (Other candidates: GPL3, MIT/BSD, feedback welcome)

## Usage

Retrieve the latest two posts on your server's public timeline:

```
tundra mastodon:timeline --limit 2
```

Tundra is still in _very_ early development, with only limited functionality.

## Installation

Tundra requires PHP 8.1 or higher.

To get started, clone it and install dependencies:

```
git clone git@github.com:beryllium/tundra.git
cd tundra
composer install
```

### Authentication

At this point, it should be possible to authenticate against your Mastodon server:

```
bin/tundra mastodon:auth
```

This will start the interactive authentication process. It will ask for your
username, which you can specify as either just your login name ("tobert", for
example), or your full Mastodon address ("@tobert@example.com").

If you enter just your login name, a separate prompt will ask for your server's
domain name.

Once that part is complete, Tundra will reach out and register itself as a
client on your server. Then, it will generate a "client" access token.

Using the client access token, it will generate a URL for you to authorize
Tundra to access your account. This will involve clicking a URL to your Mastodon
server and then clicking the "Authorize" button. This will take you to a page
that shows your authorization code.

Copy that authorization code and paste it into the prompt in Tundra, and then
Tundra will request a User Access Token from your server.

If everything works as designed, Tundra will output some settings for you to
put in the `.env` file in Tundra's project root. It will also offer to create or
append to the `.env` file on your behalf.

The .env file should look like:

```
MASTODON_SERVER=your.mastodon.instance
MASTODON_USERNAME=your-username
MASTODON_APP_ID=...
MASTODON_CLIENT_ID=...
MASTODON_CLIENT_SECRET=...
MASTODON_CLIENT_ACCESS_TOKEN=...
MASTODON_USER_TOKEN=...
```

## Commands

**mastodon:auth**: Documented above.

**mastodon:timeline**: Query the various timelines of your server.

### Coming Soon:

**mastodon:faves**: Query your own list of Favourites

**mastodon:bookmarks**: Query your own list of Bookmarks

## Development

### Making API Requests

Once you have a `MastodonClient` instance, you'll want to make requests with it.

The `->request()` method makes heavy use of named parameters.

For example, a regular `GET` request would look something like:

```php
$result = $client->request(
    apiMethod: 'timelines/' . $options['timeline'],
    local: (bool) ($options['only-local'] ?? false),
    remote: (bool) ($options['only-remote'] ?? false),
    onlyMedia: (bool) ($options['only-media'] ?? false),
    maxId: $options['max-id'] ?? null,
    sinceId: $options['since-id'] ?? null,
    minId: $options['min-id'] ?? null,
    listId: $options['list-id'] ?? null,
    server: $options['server'] ?? null,
    limit: $options['limit'] ?? null,
);
```

These parameter names correspond to the names in the Mastodon documentation.

https://docs.joinmastodon.org/methods/timelines/

Making a `POST` request is a bit different. This is because there might be a
circumstance where a `POST` request may need to be made to a URL with
`GET`-style query parameters.

Therefore, the `POST` data is added under a special key called, of course,
`postData:`. Also, an httpMethod parameter is necessary to inform the client that
this is a `POST` request.

```php
$result = $client->request(
    httpMethod: 'POST',
    apiMethod: 'apps',
    postData: [
        'client_name' => $appName,
        'redirect_uris' => $redirectUri,
        'scopes' => $scopes,
        'website' => $website,
    ]
);
```

Sometimes, you may need to make a request that lives outside of the `/api/v1`
route.

To do that, you can use the `path` override instead of specifying the
`apiMethod`.

```php
$result = $client->request(
    httpMethod: 'POST',
    path: '/oauth/token',
    postData: [
        'client_id' => $client->clientId,
        'client_secret' => $client->clientSecret,
        'redirect_uri' => self::DEFAULT_REDIRECT_URI,
        'grant_type' => 'client_credentials',
    ],
);
```

In all of the above cases, `->request()` should return an array.

The "correct" way to handle this array is to use it as the constructor parameter
for the entity class that corresponds to the request you were making.

For example, if your request was to `apps`, you would instantiate an
`Application` object:

```php
$app = new \Whateverthing\Tundra\Api\Mastodon\Entities\Application($result);
```

And this will allow PHP to handle the parsing and assignment of values for you.

(This may result in some exceptions or errors. This aspect has not been refined
to a great degree, but it does seem to work for the current happy-path
scenarios.)