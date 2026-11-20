<?php
/**
 * FINORA - Master Database Seeder
 *
 * Runs all seeders in orchestrated dependency order.
 *
 * Usage:
 *   php database/seeders/DatabaseSeeder.php
 *
 * @package Finora\Database\Seeders
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
require_once __DIR__ . '/SeederInterface.php';
require_once __DIR__ . '/CategorySeeder.php';
require_once __DIR__ . '/DemoUserSeeder.php';

use App\Core\Database;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DemoUserSeeder;

class DatabaseSeeder
{
    public static function run(): void
    {
        echo "======================================================\n";
        echo "           FINORA MASTER DATABASE SEEDER              \n";
        echo "======================================================\n\n";

        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $categorySeeder = new CategorySeeder();
            $categorySeeder->run($pdo);

            $demoUserSeeder = new DemoUserSeeder();
            $demoUserSeeder->run($pdo);

            $pdo->commit();

            echo "\n [SUCCESS] Database successfully seeded with demo profile!\n";
            echo " Demo Login: demo@finora.test\n";
            echo " Password:   FinoraDemo123!\n\n";
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "\n [ERROR] Seeding failed: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

// Execute Seeder
DatabaseSeeder::run();
