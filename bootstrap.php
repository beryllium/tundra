<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = require __DIR__ . '/app/env.php';
$container = require __DIR__ . '/app/container.php';

return $container;