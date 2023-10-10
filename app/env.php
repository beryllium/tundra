<?php

use Symfony\Component\Dotenv\Dotenv;

const TUNDRA_ROOT = __DIR__ . '/..';
const TUNDRA_ENV_FILE = TUNDRA_ROOT . '/.env';

$dotenv = new Dotenv();
$dotenv->usePutenv();
$dotenv->load(__DIR__ . '/../.env');

return $dotenv;