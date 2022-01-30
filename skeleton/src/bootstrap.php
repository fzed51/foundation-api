<?php
declare(strict_types=1);

use Slim\App;

chdir(__DIR__ . "/foundation-api");

require './vendor/autoload.php';

// Instantiate the app
$settings = require './config/settings.php';

$app = new App($settings);

/**
 * Set up handlers
 * @var callable $handlError
 */
$handlError = require __DIR__ . '/handlers.php';
$handlError($app);

/**
 * Set up dependencies
 * @var callable $handlDependencies
 */
$handlDependencies = require __DIR__ . '/dependencies.php';
$handlDependencies($app);

/**
 * Register middleware
 * @var callable $handlDependencies
 */
$handlMiddleware = require __DIR__ . '/middleware.php';
$handlMiddleware($app);

/**
 * Register routes
 * @var callable $handlDependencies
 */
$handlRoute = require __DIR__ . '/routes.php';
$handlRoute($app);

// Run app
$app->run();
