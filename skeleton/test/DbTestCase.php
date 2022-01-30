<?php
declare(strict_types=1);

namespace Test;

use Helper\DbQuickUse;
use Helper\PDOFactory;
use PDO;

/**
 * Class TestCase de base pour les tests du projet
 */
class DbTestCase extends TestCase
{

    private static ?PDO $pdo = null;
    private ?DbQuickUse $query = null;

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        if (self::$pdo === null) {
            // TODO : initialisation du PDO de test
            //self::$pdo = PDOFactory::pgsql("db_test", "127.0.0.1", "postgres", "root", 5434);
        }
        return self::$pdo;
    }

    /**
     * @return DbQuickUse
     */
    public function query(): DbQuickUse
    {
        if ($this->query === null) {
            $this->query = new DbQuickUse($this->getPdo());
        }
        return $this->query;
    }
}
