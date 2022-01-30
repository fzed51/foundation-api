<?php
declare(strict_types=1);

namespace Test;

use Helper\DbQuickUse;
use PDO;

/**
 * Class DataCleaner
 * @package Test
 */
class DataCleaner
{
    private DbQuickUse $query;

    /**
     * DataGenerator constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->query = new DbQuickUse($pdo);
    }

}
