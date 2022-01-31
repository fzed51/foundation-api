<?php
declare(strict_types=1);

namespace Cli;

/**
 * fonction d'interaction avec l'utilisateur en ligne de commande
 */
class Cli
{

    /**
     * ecrire un message et sauter une ligne
     * @param string $message
     * @return void
     */
    public static function writeLn(string $message): void
    {
        self::write($message);
        echo PHP_EOL;
    }

    /**
     * ecrire un message
     * @param string $message
     * @return void
     */
    public static function write(string $message): void
    {
        echo $message;
    }
}