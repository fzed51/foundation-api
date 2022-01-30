<?php

declare(strict_types=1);

namespace Api\Utilities;

use JsonException;

/**
 * Class ApiHttpResponse
 * Reponse retourner Par ApihttpClient
 * @package Api\Utilities
 */
class ApiHttpResponse
{
    /** @var string|null */
    private ?string $data;
    /** @var int */
    private int $status;
    /** @var array<string,string[]> */
    private array $headers;

    /**
     * ApiHttpResponse constructor.
     * @param int $status
     * @param array<string,string[]> $headers
     * @param ?string $data
     */
    public function __construct(int $status, array $headers, ?string $data = null)
    {
        $this->data = $data;
        $this->headers = $headers;
        $this->status = $status;
    }

    /**
     * @return mixed
     * @throws JsonException
     */
    public function getData(bool $isJson = true)
    {
        if ($this->data === null) {
            return null;
        }
        if ($isJson) {
            return json_decode($this->data, true, 512, JSON_THROW_ON_ERROR);
        }
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status < 400;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array<string,string[]>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }


    /**
     * @param string $header
     * @return array<string>
     */
    public function getHeader(string $header): array
    {
        return $this->headers[$header] ?? [];
    }
}
