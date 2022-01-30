<?php
declare(strict_types=1);

use Api\Middlewares\StatMiddleware;
use Slim\App;

return static function (App $app) {
    $app->add(StatMiddleware::class);
};
