<?php

use Symfony\Component\Dotenv\Dotenv;

const TUNDRA_ROOT = __DIR__ . '/..';
const TUNDRA_ENV_FILE = TUNDRA_ROOT . '/.env';

$dotenv = new Dotenv();
$dotenv->usePutenv();

try {
    $dotenv->load(realpath(TUNDRA_ENV_FILE));
} catch (\Symfony\Component\Dotenv\Exception\FormatException $e) {
    die('Formatting error found in your .env file! ' . realpath(TUNDRA_ENV_FILE));
} catch (\Symfony\Component\Dotenv\Exception\PathException $e) {
    die('Your .env file could not be found or is not readable! ' . realpath(TUNDRA_ENV_FILE));
}

return $dotenv;