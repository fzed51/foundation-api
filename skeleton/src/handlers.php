<?php /** @noinspection OnlyWritesOnParameterInspection */
declare(strict_types=1);

namespace Handlers;

use Api\ResponseFormatter;
use Exception;
use HttpException\HttpException;
use JsonException;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Throwable;

/**
 * @param Response $response
 * @param int $status
 * @param string $message
 * @param null|object|mixed[] $detail
 * @return Response
 */
function formatResponse(Response $response, int $status, string $message, $detail = null): Response
{
    $formater = new ResponseFormatter($response);
    return $formater->formatDirectError($status, $message, $detail)
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'POST, GET, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, X-AUTHTOKEN');
}

/**
 * @param string $message
 * @return string
 */
function utf8Encode(string $message):string
{
    if (strpos($message, "\\u") === false) {
        return utf8_encode($message);
    }
    return $message;
}

/**
 * log une exception
 * @param LoggerInterface $logger
 * @param Throwable $err
 * @throws JsonException
 */
function logException(LoggerInterface $logger, Throwable $err): void
{
    $logger->debug(
        "Exception : " . json_encode([
            'exception' => get_class($err),
            'message' => utf8Encode($err->getMessage()),
            'file' => $err->getFile(),
            'line' => $err->getLine(),
            'trace' => $err->getTrace(),
        ], JSON_THROW_ON_ERROR)
    );
}

/**
 * format les éléments de base d'une requête
 * @param \Slim\Http\Request $request
 * @return string
 */
function getRequestBasicsFormatted(Request $request): string
{
    $protocol = $request->getProtocolVersion();
    $method = (string)$request->getMethod();
    $url = (string)$request->getUri();
    return "HTTP/$protocol $method $url";
}

/**
 * format l'entête d'une requête
 * @param \Slim\Http\Request $request
 * @return string
 */
function getRequestHeaderFormatted(Request $request): string
{
    $headers = $request->getHeaders();
    $strHeaders = '';
    foreach ($headers as $key => $values) {
        $strHeaders .= PHP_EOL . $key . ': ' . implode(';', $values);
    }
    return trim($strHeaders);
}

/**
 * log une requête
 * @param \Psr\Log\LoggerInterface $logger
 * @param \Slim\Http\Request $request
 */
function logRequest(LoggerInterface $logger, Request $request): void
{
    $logger->notice(getRequestBasicsFormatted($request));
    $logger->debug(getRequestHeaderFormatted($request));
    $body = (string)$request->getBody();
    if (!empty($body)) {
        $logger->debug($body);
    }
}

return static function (App $app) {

    $container = $app->getContainer();

    $container['errorHandler'] = static function (Container $container) {
        return static function (Request $request, Response $response, Exception $exception) use ($container): Response {
            /** @var LoggerInterface $logger */
            $logger = $container->get('logger');
            if ($exception instanceof HttpException) {
                $logger->warning(
                    "Une erreur s'est produite :" . PHP_EOL .
                    "(" . $exception->getCode() . ") " . $exception->getMessage()
                );
                logException($logger, $exception);
                logRequest($logger, $request);
                return formatResponse(
                    $response,
                    $exception->getCode(),
                    $exception->getMessage()
                );
            }
            $logger->error(
                "Une erreur interne s'est produite :" . PHP_EOL .
                "(" . $exception->getCode() . ") " . $exception->getMessage()
            );
            logException($logger, $exception);
            logRequest($logger, $request);
            if ($container->get('settings')['displayErrorDetails']) {
                return formatResponse(
                    $response,
                    500,
                    "Erreur interne",
                    [
                        'exception' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => $exception->getTrace(),
                    ]
                );
            }
            return formatResponse($response, 500, "Erreur interne");
        };
    };

    $container['phpErrorHandler'] = static function (Container $container) {
        return static function (Request $request, Response $response, Throwable $error) use ($container): Response {
            /** @var LoggerInterface $logger */
            $logger = $container->get('logger');
            $logger->critical(
                "Une erreur interne PHP s'est produite :" . PHP_EOL .
                "(" . $error->getCode() . ") " . $error->getMessage()
            );
            logException($logger, $error);
            logRequest($logger, $request);
            return formatResponse(
                $response,
                500,
                "Erreur interne"
            );
        };
    };

    $container['notFoundHandler'] = static function (Container $container) {
        return static function (Request $request, Response $response) use ($container): Response {
            /** @var LoggerInterface $logger */
            $logger = $container->get('logger');
            $logger->notice(
                "Chemin inexistant :({$request->getMethod()}) {$request->getUri()}"
            );
            return formatResponse(
                $response,
                404,
                "Chemin non trouvé"
            );
        };
    };

    $container['notAllowedHandler'] = static function (Container $container) {
        return static function (Request $request, Response $response, array $methods) use ($container): Response {
            /** @var LoggerInterface $logger */
            $logger = $container->get('logger');
            $logger->warning(
                "Méthode non autorisée :({$request->getMethod()}) {$request->getUri()}" . PHP_EOL .
                "sont autorisée : " . implode(', ', $methods)
            );
            return formatResponse(
                $response,
                405,
                "Méthode non autorisée"
            );
        };
    };
};
