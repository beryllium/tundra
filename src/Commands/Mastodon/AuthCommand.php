<?php

namespace Whateverthing\Tundra\Commands\Mastodon;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Whateverthing\Tundra\Api\Mastodon\Entities\Application;
use Whateverthing\Tundra\Api\Mastodon\MastodonClient;

class AuthCommand extends Command
{
    public const DEFAULT_REDIRECT_URI = 'urn:ietf:wg:oauth:2.0:oob';
    public const DEFAULT_SCOPES = 'read write push';

    public function __construct()
    {
        parent::__construct('mastodon:auth');
    }

    public function configure()
    {
        $this->setDescription('Authenticate with a Mastodon instance');
        $this->setHelp('Handles the annoyingly complicated auth dance and shows the values to add to .env');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Beginning Mastodon Authentication.');
        $output->writeln('');
        $helper = $this->getHelper('question');

        $usernameRaw = $this->getUsernameRaw($input, $output, $helper);
        $username = $this->getUsernameFromRawUsername($usernameRaw);
        $serverName = $this->getServerDomainFromUsername($usernameRaw);
        if (!$serverName) {
            $serverName = $this->getServerDomain($input, $output, $helper);
        }
        
        if (!$serverName) {
            $output->writeln('<error>> Failed to provide a server name</error>');
            
            return self::INVALID;
        }

        // Authenticate the application with the server
        $client = $this->connectToServer($input, $output, $helper, $serverName, $username);
        $authorizedClient = $this->getClientAuthorization($input, $output, $helper, $client);

        if ($authorizedClient->verifyCredentials()) {
            $output->writeln('> Application Credentials Verified');
        } else {
            $output->writeln('<error>> Failed to verify application credentials!</error>');
            $output->writeln('Please try this flow again in a few minutes to see if it will work.');
            
            return self::INVALID;
        }
        
        // Authenticate the user with the server
        // (Maybe this should be optional? Users might want to interact with public info and skip user auth)
        $authorizedUserClient = $this->getUserAuthorization($input, $output, $helper, $authorizedClient);

        if (!$authorizedUserClient) {
            $output->writeln('<error>> Failed to authorize user account access</error>');

            return self::INVALID;
        }
        
        $this->handleEnvUpdateMessaging($input, $output, $helper, $authorizedUserClient);
        
        return self::SUCCESS;
    }

    private function getServerDomain(InputInterface $input, OutputInterface $output, mixed $helper): string
    {
        $output->writeln('> Getting Mastodon Server Domain');
        $serverDomain = getenv(MastodonClient::CONFIG_SERVER) ?? null;

        if ($serverDomain) {
            $output->writeln('> Found Server Domain: ' . $serverDomain);
            
            return $serverDomain;
        }

        $question = new Question('Enter the domain name of your Mastodon server: ');
        $serverDomain = $helper->ask($input, $output, $question);

        return $serverDomain;
    }

    private function getUsernameRaw(InputInterface $input, OutputInterface $output, mixed $helper)
    {
        $output->writeln('> Getting Mastodon Username');
        $username = getenv(MastodonClient::CONFIG_USERNAME) ?? null;

        if ($username) {
            $output->writeln('> Found Username: ' . $username);
            return $username;
        }

        $question = new Question('Enter your Mastodon username: ');
        $username = $helper->ask($input, $output, $question);

        return $username;
    }

    private function getServerDomainFromUsername(string $usernameRaw)
    {
        $matches = [];
        preg_match('/^@[^@]+@(.*)$/', $usernameRaw, $matches);

        return $matches[1] ?? '';
    }

    private function connectToServer(InputInterface $input, OutputInterface $output, mixed $helper, string $serverName, ?string $username)
    {
        $appId = getenv(MastodonClient::CONFIG_APP_ID);
        $clientId = getenv(MastodonClient::CONFIG_CLIENT_ID);
        $clientSecret = getenv(MastodonClient::CONFIG_CLIENT_SECRET);

        if ($appId && $clientId && $clientSecret) {
            $output->writeln('> Server Connection Detected');
            return new MastodonClient(
                server: $serverName,
                username: $username,
                appId: $appId,
                clientId: $clientId,
                clientSecret: $clientSecret
            );
        }

        $appName = MastodonClient::APP_NAME;
        $redirectUri = self::DEFAULT_REDIRECT_URI;
        $scopes = self::DEFAULT_SCOPES;
        $website = MastodonClient::APP_URL;

        $client = new MastodonClient(server: $serverName, username: $username);
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

        $app = new Application(...$result);

        return new MastodonClient(
            server: $serverName,
            username: $username,
            appId: $app->id,
            clientId: $app->client_id,
            clientSecret: $app->client_secret
        );
    }

    private function getClientAuthorization(
        InputInterface $input,
        OutputInterface $output,
        mixed $helper,
        MastodonClient $client
    ) {
        $clientToken = getenv(MastodonClient::CONFIG_CLIENT_ACCESS_TOKEN);

        if ($clientToken) {
            $output->writeln('> Client Access Token Detected');

            return new MastodonClient(
                server: $client->server,
                username: $client->username,
                appId: $client->appId,
                clientId: $client->clientId,
                clientSecret: $client->clientSecret,
                clientAccessToken: $clientToken
            );
        }

        $output->writeln('> Requesting Client Access Token');
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

        return new MastodonClient(
            server: $client->server,
            username: $client->username,
            appId: $client->appId,
            clientId: $client->clientId,
            clientSecret: $client->clientSecret,
            clientAccessToken: $result['access_token'] ?? null
        );
    }

    private function getUserAuthorization(
        InputInterface $input,
        OutputInterface $output,
        mixed $helper,
        MastodonClient $client
    ) {
        $output->writeln('<info>> Beginning User Authorization </info>');

        $userAccessToken = getenv(MastodonClient::CONFIG_USER_TOKEN);

        if ($userAccessToken) {
            $output->writeln('> Authorization Token Found!');

            return new MastodonClient(
                server: $client->server,
                username: $client->username,
                appId: $client->appId,
                clientId: $client->clientId,
                clientSecret: $client->clientSecret,
                clientAccessToken: $client->clientAccessToken,
                userAccessToken: $userAccessToken
            );
        }

        $url = "https://{$client->server}/oauth/authorize?" . http_build_query(
                [
                    'client_id' => $client->clientId,
                    'client_secret' => $client->clientSecret,
                    'scope' => self::DEFAULT_SCOPES,
                    'redirect_uri' => self::DEFAULT_REDIRECT_URI,
                    'response_type' => 'code',
                ]
            );

        $output->writeln(<<<EOT
        
            Tundra requires you to grant access to your user account on your Mastodon server.
            
            In order to do this, click the URL shown below.
            
            Once authorization has been granted, Mastodon should show you an "Authorization Code".
            
            Copy that code and paste it into the Tundra text prompt to continue.
            
            <href=$url>$url</>
            
        EOT
        );

        $question = new Question('Enter the Authorization Code: ');
        $authorizationCode = $helper->ask($input, $output, $question);

        if (!$authorizationCode) {
            $output->writeln('> Code not provided. Please try again.');

            return null;
        }

        $result = $client->request(
            httpMethod: 'POST',
            path: '/oauth/token',
            postData: [
                'client_id' => $client->clientId,
                'client_secret' => $client->clientSecret,
                'redirect_uri' => self::DEFAULT_REDIRECT_URI,
                'grant_type' => 'authorization_code',
                'code' => $authorizationCode,
                'scope' => self::DEFAULT_SCOPES,
            ]
        );

        $userAccessToken = $result['access_token'] ?? null;

        return new MastodonClient(
            server: $client->server,
            username: $client->username,
            appId: $client->appId,
            clientId: $client->clientId,
            clientSecret: $client->clientSecret,
            clientAccessToken: $client->clientAccessToken,
            userAccessToken: $userAccessToken
        );
    }

    private function handleEnvUpdateMessaging(
        InputInterface $input,
        OutputInterface $output,
        mixed $helper,
        MastodonClient $client
    ) {
        $output->writeln('> Updates may be required to your .env file');

        $envContents = <<<EOT
        MASTODON_SERVER={$client->server}
        MASTODON_USERNAME={$client->username}
        MASTODON_APP_ID={$client->appId}
        MASTODON_CLIENT_ID={$client->clientId}
        MASTODON_CLIENT_SECRET={$client->clientSecret}
        MASTODON_CLIENT_ACCESS_TOKEN={$client->clientAccessToken}
        MASTODON_USER_TOKEN={$client->userAccessToken}
        EOT;

        $output->writeln($envContents);

        if (!file_exists(TUNDRA_ENV_FILE)) {
            $question = new ConfirmationQuestion('Would you like to create the .env file at ' . realpath(TUNDRA_ENV_FILE) . '? (Y/n): ');
            $answer = $helper->ask($input, $output, $question);

            if ($answer) {
                file_put_contents(TUNDRA_ENV_FILE, $envContents);
            }

            if (!file_exists(TUNDRA_ENV_FILE)) {
                $output->writeln('Uh oh! Something went wrong! Please create the .env file manually.');

                return false;
            }

            return true;
        }

        $question = new ConfirmationQuestion('Would you like to append these values to the .env file at ' . realpath(TUNDRA_ENV_FILE) . '? (Y/n): ');
        $answer = $helper->ask($input, $output, $question);

        if ($answer) {
            file_put_contents(TUNDRA_ENV_FILE, "\n" . $envContents, FILE_APPEND);
            $output->writeln('> Contents Appended');

            return true;
        }

        $output->writeln('> Manual handling chosen. Nothing to do.');

        return true;
    }

    private function getUsernameFromRawUsername(?string $usernameRaw)
    {
        if (!$usernameRaw) {
            return null;
        }

        $matches = [];
        preg_match('/^@?([^@]+)(@.*|)$/', $usernameRaw, $matches);

        return $matches[1] ?? null;
    }
}