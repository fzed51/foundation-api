<?php
declare(strict_types=1);

namespace Test;

use RuntimeException;
use Slim\Http\Body;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;
use Slim\Http\Uri;

/**
 * Class TestCase de base pour les tests du projet
 */
class ControllerTestCase extends ActionTestCase
{
     /**
     * @param Response $response
     * @return array<string,mixed>
     */
    private function getResponse(Response $response): array
    {
        $status = $response->getStatusCode();
        $header = $response->getHeaders();
        $body = (string)$response->getBody();
        return [
            'status' => $status,
            'header' => $header,
            'body' => $body
        ];
    }

    /**
     * test et retourne la data de la réponse
     * @param Response $response
     * @return mixed
     */
    protected function assertSuccessResponseReturnData(Response $response)
    {
        $simpleResponse = $this->getResponse($response);
        self::assertEquals(StatusCode::HTTP_OK, $simpleResponse['status']);
        $body = $simpleResponse['body'];
        if (!empty($body)) {
            /** @noinspection JsonEncodingApiUsageInspection */
            $fullData = json_decode($body, true);
            self::assertSame(json_last_error(), 0);
            return $fullData;
        }
        return null;
    }

    /**
     * test et retourne la data de la réponse
     * @param Response $response
     * @param string $redirectUrl
     * @return mixed
     */
    protected function assertRedirectResponseReturnData(Response $response, string $redirectUrl)
    {
        $simpleResponse = $this->getResponse($response);
        self::assertEquals(StatusCode::HTTP_FOUND, $simpleResponse['status']);
        $location = $response->getHeader("Location");
        self::assertEquals($redirectUrl, $location[0]);
        return null;
    }


    /**
     * @param string $uri
     * @param mixed[] $data
     * @param array<string,string> $headers
     * @return Request
     */
    protected function makeJsonPostRequest(string $uri, array $data, array $headers = []): Request
    {
        $headers['Content-type'] = 'application/json';
        /** @noinspection JsonEncodingApiUsageInspection */
        $body = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('impossible de parse le body en json');
        }
        return $this->makePostRequest($uri, $body, $headers);
    }

    /**
     * @param string $uri
     * @param string $data
     * @param array<string,string> $headers
     * @return Request
     */
    protected function makePostRequest(string $uri, string $data, array $headers = []): Request
    {
        $method = "POST";
        $uri = Uri::createFromString($uri);
        $header = new Headers($headers);
        $serverParams = [];
        $cookies = [];
        return new Request($method, $uri, $header, $cookies, $serverParams, $this->makeBody($data));
    }

    /**
     * @param string $content
     * @return Body
     */
    protected function makeBody(string $content = ""): Body
    {
        $stream = fopen('data://text/plain;base64,' . base64_encode($content), 'rb');
        return new Body($stream);
    }


    /**
     * @param string $uri
     * @param array<string,string> $headers
     * @return Request
     */
    protected function makeGetRequest(string $uri, array $headers = []): Request
    {
        $method = "GET";
        $uri = Uri::createFromString($uri);
        $headers = new Headers($headers);
        $serverParams = [];
        $cookies = [];
        return new Request($method, $uri, $headers, $cookies, $serverParams, $this->makeBody());
    }

    /**
     * @param string $uri
     * @param array<string,string> $headers
     * @return Request
     */
    protected function makeDeleteRequest(string $uri, array $headers = []): Request
    {
        $method = "DELETE";
        $uri = Uri::createFromString($uri);
        $headers = new Headers($headers);
        $serverParams = [];
        $cookies = [];
        return new Request($method, $uri, $headers, $cookies, $serverParams, $this->makeBody());
    }

    /**
     * @return Response
     */
    protected function makeResponse(): Response
    {
        return new Response();
    }

    /**
     * @param string $url
     * @param array<string,mixed> $query
     * @return string
     */
    protected function makeUri(string $url, array $query = []): string
    {
        return $url . (empty($query) ? '' : ('?' . http_build_query($query)));
    }
}
