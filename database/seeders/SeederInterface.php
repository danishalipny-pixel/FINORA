<?php
/**
 * FINORA - Database Seeder Interface
 *
 * Contract for database seeders.
 *
 * @package Finora\Database\Seeders
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

interface SeederInterface
{
    /**
     * Run the database seed operations.
     */
    public function run(PDO $pdo): void;
}
