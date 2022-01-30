<?php
declare(strict_types=1);

namespace Api\Utilities;

use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class ApiHttpClient
 * @package Api\Utilities
 */
class ApiHttpClient implements ApiHttpClientInterface
{

    /**
     * @var array<string, mixed>
     * Tableaux de paramètre pour les different curl
     */
    protected array $curlsParam = [];

    /**
     * @var resource
     * Resource qui est donner par curl_multi_init()
     */
    protected $curlMulHand;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var array<string,resource>
     * liste des curl init
     */
    protected array $curls = [];
    /**
     * @var array<string,ApiHttpResponse>
     * tableaux de résultat des curls
     */
    protected array $curlResult;

    /**
     * @var bool
     * indique si le process est fini
     */
    protected bool $endOfProcess;

    /**
     * ApiHttpClient constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function addParamRequest(string $url, string $methode, array $headers, ?string $data = null): string
    {
        $clef = $this->getClef(8);
        $param = ["url" => $url, "headers" => $headers, "methode" => $methode, "data" => $data];
        $this->curlsParam[$clef] = $param;
        return $clef;
    }

    /**
     * @param int $length
     * @return string
     */
    private function getClef(int $length): string
    {
        $data = openssl_random_pseudo_bytes($length, $strong);
        if (false === $strong || false === $data) {
            throw new RuntimeException("Un problème est survenu lors d'une génération cryptographique.");
        }
        return bin2hex($data);
    }

    /**
     * @inheritDoc
     */
    public function execAll(): void
    {
        $this->endOfProcess = false;
        $active = null;
        $this->initAll();
        curl_multi_exec($this->curlMulHand, $active);
    }

    /**
     * @return bool
     */
    private function initAll(): bool
    {
        $this->curlMulHand = curl_multi_init();
        foreach ($this->curlsParam as $clef => $curlparam) {
            $curl = $this->initNewCurl($curlparam);
            if (!$curl) {
                $this->logger->error("Problème dans l'initialisation d'un curl", ["curlparam" => $curlparam]);
                return false;
            }
            $this->curls[$clef] = $curl;
            curl_multi_add_handle($this->curlMulHand, $curl);
        }
        return true;
    }

    /**
     * @param array<string,mixed> $curlparam
     * @return false|resource
     */
    private function initNewCurl(array $curlparam)
    {
        $curl = curl_init($curlparam["url"]);
        if (!$curl) {
            return false;
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $curlparam["headers"]);
        curl_setopt($curl, CURLOPT_POST, $curlparam["post"]);
        $this->setCurlMethode($curlparam["methode"], $curl, $curlparam["data"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        return $curl;
    }

    /**
     * @param string $methode
     * @param resource $curl
     * @param string $data
     */
    private function setCurlMethode(string $methode, $curl, string $data): void
    {
        switch ($methode) :
            case ApiHttpMethod::GET:
                break;
            case ApiHttpMethod::POST:
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case ApiHttpMethod::DELETE:
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $methode);
                break;
        endswitch;
    }

    /**
     * @inheritDoc
     */
    public function getResult(string $clef): ApiHttpResponse
    {
        if (!$this->endOfProcess) {
            $this->waitResult();
        }
        if (!array_key_exists($clef, $this->curlResult)) {
            $this->logger->error("La clef curl n'éxiste pas", ["clef Curl" => $clef]);
            throw new RuntimeException("La clef curl n'éxiste pas");
        }
        return $this->curlResult[$clef];
    }

    /**
     * @inheritDoc
     */
    public function waitResult(): void
    {
        $active = null;
        do {
            $status = curl_multi_exec($this->curlMulHand, $active);
            if ($active) {
                curl_multi_select($this->curlMulHand);
            }
        } while ($active && $status == CURLM_OK);
        $result = [];
        //Verifier les erreurs
        if ($status !== CURLM_OK) {
            $this->logger->error("Error Curl", ["error" => curl_multi_strerror($status)]);
            throw new RuntimeException("Une erreur a eu lieu avec le serveur");
        }
        foreach ($this->curls as $clef => $curl) {
            /** @var string|null $response */
            $response = curl_multi_getcontent($curl);
            if ($response === null) {
                $this->logger->error("Curl error", [curl_error($curl)]);
                $result[$clef] = new ApiHttpResponse(500, [], curl_error($curl));
            } else {
                $result[$clef] = $this->parseResponse($response);
            }
            curl_multi_remove_handle($this->curlMulHand, $curl);
        }
        $this->curlResult = $result;
        $this->endOfProcess = true;
        $this->closeMultiCurl();
    }

    private function parseResponse(?string $response): ApiHttpResponse
    {
        return new ApiHttpResponse(200, [], '');
    }

    /**
     *
     */
    private function closeMultiCurl(): void
    {
        if (is_resource($this->curlMulHand)) {
            curl_multi_close($this->curlMulHand);
        }
    }

    /**
     * @inheritDoc
     */
    public function curlUnique(string $url, string $methode, array $headers, ?string $data = null): ApiHttpResponse
    {
        $curl = $this->initNewCurl([
            "url" => $url,
            "headers" => $headers,
            "methode" => $methode,
            "data" => $data,
            "post" => $methode === ApiHttpMethod::POST
        ]);
        if (!$curl) {
            throw new RuntimeException("Erreur de curl ");
        }
// Vérifie les erreurs et affiche le message d'erreur
        $curlResult = curl_exec($curl);
        $status = curl_errno($curl);
        if ($status !== CURLE_OK) {
            $this->logger->error("Error Curl", ["error" => curl_strerror($status)]);
            throw new RuntimeException("Une erreur a eu lieu avec le serveur");
        }
        if ($curlResult) {
            $response = $this->parseResponse($curlResult);
        } else {
            $response = new ApiHttpResponse(500, [], curl_error($curl));
        }
        curl_close($curl);
        return $response;
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
}
