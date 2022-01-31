<?php
declare(strict_types=1);

namespace Bin;

use Cli\Cli;
use Cli\Commande;
use Cli\Exceptions\CliMessage;
use Console\Options\Option;
use Console\Options\OptionParser;
use RuntimeException;

if (is_dir('vendor')) {
    require_once 'vendor/autoload.php';
} elseif (is_dir(__DIR__ . '/../../vendor')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} elseif (is_dir(__DIR__ . '/../../../../vendor')) {
    require_once __DIR__ . '/../../../../vendor/autoload.php';
} else {
    throw new RuntimeException("Le dossier vendor n'est pas trouvé");
}

$options = new OptionParser([
    (new Option('help', 'h'))->setType(Option::T_FLAG),
    (new Option('init', 'i'))->setType(Option::T_FLAG)
]);

try {
    $command = new Commande();
    if (isset($options['help']) && $options['help']) {
        $command->usage();
    } elseif (isset($options['init']) && $options['init']) {
        $command->initialisation('.');
    } else {
        $command->usage();
    }
} catch (CliMessage $ex) {
    Cli::writeLn($ex->getMessage());
}
