<?php
declare(strict_types=1);

use Helper\PDOFactory;
use Migration\MigrationCore;

require __DIR__ . '/../vendor/autoload.php';

$pdo = PDOFactory::pgsql("gru_psrv", "127.0.0.1", "postgres", "root", 5434);
$migration = new MigrationCore();
$migration
    ->setPdo($pdo)
    ->setProvider('postgres')
    ->setMigrationDirectory(__DIR__ . "/../db/migrations")
    ->run();
unset($migration, $pdo);
