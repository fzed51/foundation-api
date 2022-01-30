<?php
declare(strict_types=1);

namespace Api\Middlewares;

use Api\Middleware;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class StatMiddleware
 * @package Api\Middlewares
 */
class StatMiddleware extends Middleware
{

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, callable $next): Response
    {
        $start = microtime(true);
        /** @var Response $response */
        $response = $next($request, $response);
        $stop = microtime(true);
        $chrono = sprintf("%.3Fs", ($stop - $start));
        return $response->withHeader('X-TIMERUN', [$chrono]);
    }
}
