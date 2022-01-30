<?php
declare(strict_types=1);

namespace Test;

use Faker\Factory;
use Faker\Generator;
use Helper\DbQuickUse;
use PDO;

/**
 * Class DataGenerator
 * @package Test
 */
class DataGenerator
{
    private Generator $facker;
    private DbQuickUse $query;

    /**
     * DataGenerator constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->facker = Factory::create('fr_FR');
        $this->query = new DbQuickUse($pdo);
    }
}
