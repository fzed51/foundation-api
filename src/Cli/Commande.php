<?php
declare(strict_types=1);

namespace Cli;

use Cli\Exceptions\CliMessage;

/**
 * Commandes pour foundation-manager
 */
class Commande
{
    /**
     * Initialisation complète d'un projet API
     * @param string $path
     * @return void
     */
    public function initialisation(string $path): void
    {
        self::controlPath($path);
    }

    /**
     * Control si le path est un chemin valide
     * @param string $path
     * @return void
     */
    private static function controlPath(string $path): void
    {
        if (!is_dir($path) || !is_file($path . '/composer.json')) {
            throw new CliMessage("'$path' n'est pas un chemin valide");
        }
    }

    /**
     * Affiche l'aide
     * @return void
     */
    public function usage(): void
    {
        $usage = <<<USAGE
Usage :
--------
foundation-manager [commande]

Commandes :
------------
* sans commande
  -help -h      : affiche ce message
* -init -i      : installation complète du template API
USAGE;
        $messages = explode(PHP_EOL, $usage);
        foreach ($messages as $message) {
            Cli::writeLn(rtrim($message));
        }
    }
}