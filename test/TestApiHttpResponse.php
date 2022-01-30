<?php
declare(strict_types=1);

namespace Test;

/**
 * test de ApiHttpResponse
 */
class TestApiHttpResponse extends TestCase
{
    /**
     * test du parsage de response
     */
    public function testParseResponse(): void
    {
        $response = <<<Rep
HTTP/1.1 200 OK
Date: Thu, 15 feb 2019 12:02:32 GMT
Server: Apache/2.0.54 (Debian GNU/Linux) DAV/2 SVN/1.1.4
Connection: close
Transfer-Encoding: chunked
Content-Type: text/html; charset=ISO-8859-1

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Voici mon site</title>
    </head>
    <body>
        <h1>Hello World! Ceci est un titre</h1>
        <p>
            Ceci est un <strong>paragraphe</strong>. Avez-vous bien compris ?
        </p>
    </body>
</html>
Rep;
        $rep = $this->parseResponse($response);
        self::assertEquals(200, $rep->getStatus());
        self::assertIsArray($rep->getHeaders());
        self::assertCount(5, $rep->getHeaders());
        self::assertEquals("<!doctype html>
<html lang=\"fr\">
    <head>
        <meta charset=\"utf-8\">
        <title>Voici mon site</title>
    </head>
    <body>
        <h1>Hello World! Ceci est un titre</h1>
        <p>
            Ceci est un <strong>paragraphe</strong>. Avez-vous bien compris ?
        </p>
    </body>
</html>", $rep->getData(false));
    }

    /**
     * @param string $response
     * @return \Api\Utilities\ApiHttpResponse
     */
    protected function parseResponse(string $response): \Api\Utilities\ApiHttpResponse
    {
        $lines = explode("\n", $response);
        if (false === $lines) {
            throw new \RuntimeException("Format de reponse http non reconu");
        }
        $line1 = array_shift($lines);
        $status = $this->parse1stLine($line1);
        $headers = [];
        $line = trim(array_shift($lines));
        while ($line !== "") {
            $headers = $this->parseHeader($headers, $line);
            $line = trim(array_shift($lines));
        }
        $body = implode("\n", $lines);
        return new \Api\Utilities\ApiHttpResponse($status, $headers, $body);
    }

    /**
     * parse la 1ere ligne de reponse http pour donner le status
     * @param string $line1
     * @return int
     */
    private function parse1stLine(string $line1): int
    {
        if (preg_match("/^([^ ]+)\s+(\d+)\s+(.*)$/", $line1, $matches) > 0) {
            return (int)$matches[2];
        }
        throw new \RuntimeException("Format de reponse http non reconu");
    }

    /**
     * @param array<string, string[]> $headers
     * @param string $line
     * @return array<string, string[]>
     */
    private function parseHeader(array $headers, string $line): array
    {
        $header = explode(':', $line, 2);
        $key = $header[0];
        $value = array_map('trim', explode(';', $header[1]));
        $headers[$key] = $value;
        return $headers;
    }

    /**
     * test du parsage de response
     */
    public function testParseResponseWith404(): void
    {
        $response = <<<Rep
HTTP/1.1 404 NOT FOUND
Date: Thu, 15 feb 2019 12:02:32 GMT
Server: Apache/2.0.54 (Debian GNU/Linux) DAV/2 SVN/1.1.4
Connection: close
Transfer-Encoding: chunked
Content-Type: text/plain; charset=ISO-8859-1

NOT FOUND
Rep;
        $rep = $this->parseResponse($response);
        self::assertEquals(404, $rep->getStatus());
    }

}