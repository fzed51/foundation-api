<?php

declare(strict_types=1);

namespace Api\Utilities;

/**
 * Interface ApiHttpClientInterface
 * Gere les appelles au api
 * @package Api\Utilities
 */
interface ApiHttpClientInterface
{
    /**
     * stocke dans un tableaux les paramettre et renvoie une clef d'identification
     * @param string $url
     * @param string $methode
     * @param array<string, mixed> $headers
     * @param string|null $data
     * @return string
     */
    public function addParamRequest(string $url, string $methode, array $headers, ?string $data = null): string;


    /**
     * Fonction qui execute tout les curls et met les retours dans un tableaux
     */
    public function execAll(): void;


    /**
     * Fonction qui attend les resultats de tout les curls
     */
    public function waitResult(): void;

    /**
     * Fonction qui retourne l'objet contenu du tableaux de résultat en fonction de la clef
     * @param string $clef
     * @return \Api\Utilities\ApiHttpResponse
     */
    public function getResult(string $clef): ApiHttpResponse;


    /**
     * @param string $url
     * @param string $methode
     * @param array<string, mixed> $headers
     * @param string|null $data
     * @return \Api\Utilities\ApiHttpResponse
     */
    public function curlUnique(string $url, string $methode, array $headers, ?string $data = null): ApiHttpResponse;
}
