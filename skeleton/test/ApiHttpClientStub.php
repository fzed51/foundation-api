<?php
declare(strict_types=1);

namespace Test;

use Api\Utilities\ApiHttpResponse;
use Api\Utilities\ApiHttpClientInterface;
use RuntimeException;

/**
 * Class ApiHttpClientStub
 */
class ApiHttpClientStub implements ApiHttpClientInterface
{
    /**
     * @var array<string,ApiHttpResponse>
     */
    private array $result;
    /**
     * @var array<int,string>
     */
    private array $clefs;
    private int $index = 0;

    /**
     * @param string $url
     * @param array<string,mixed> $headers
     * @param string $methode
     * @param string $data
     * @return string
     */
    public function addParamRequest(string $url, array $headers, string $methode, string $data = ''): string
    {

        if (!isset($this->clefs[$this->index])) {
            throw new RuntimeException("Il n'y a pas assez d'éléments dans les résultats du stub");
        }
        $clef= $this->clefs[$this->index];
        $this->index ++;
        return $clef ;
    }

    public function addResult(bool $success, string $data):void
    {
        $clef=$this->getClef(8);
        $this->clefs[]=$clef;
        $this->result[$clef]=new ApiHttpResponse($success, $data);
    }

    public function execAll(): void
    {
    }

    public function waitResult(): void
    {
    }

    /**
     * @param string $clef
     * @return ApiHttpResponse
     */
    public function getResult(string $clef): ApiHttpResponse
    {
        return $this->result[$clef];
    }

    /**
     *
     */
    public function resetResult():void
    {
        $this->result=[];
        $this->clefs=[];
        $this->index=0;
    }

    /**
     * @param string $url
     * @param array<string, mixed> $headers
     * @param string $methode
     * @param string $data
     * @return ApiHttpResponse
     */
    public function curlUnique(string $url, array $headers, string $methode, string $data = ''): ApiHttpResponse
    {
        if (!isset($this->clefs[$this->index])) {
            throw new RuntimeException("Il n'y a pas assez d'éléments dans les résultats du stub");
        }
        $clef= $this->clefs[$this->index];
        $this->index ++;
        return $this->result[$clef];
    }

    /**
     * @param int $length
     * @return string
     */
    private function getClef(int $length): string
    {
        $data = openssl_random_pseudo_bytes($length/2, $strong);
        if (false === $strong || false === $data) {
            throw new RuntimeException("Un problème est survenu lors d'une génération cryptographique.");
        }
        return bin2hex($data);
    }
}
