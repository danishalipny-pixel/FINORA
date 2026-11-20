<?php
/**
 * FINORA - Database Migration Runner
 *
 * Scans, executes, and tracks transactional database migrations.
 *
 * Usage:
 *   php database/migrations/migrate.php
 *   php database/migrations/migrate.php --status
 *
 * @package Finora\Database
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap/app.php';

use App\Core\Database;

class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsDir;

    public function __construct()
    {
        $this->pdo = Database::connection();
        $this->migrationsDir = __DIR__;
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `batch` INT NOT NULL,
            `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->pdo->exec($sql);
    }

    public function run(array $args = []): void
    {
        $action = $args[1] ?? 'migrate';

        if ($action === '--status' || $action === 'status') {
            $this->showStatus();
            return;
        }

        echo "======================================================\n";
        echo "          FINORA DATABASE MIGRATION ENGINE            \n";
        echo "======================================================\n\n";

        $applied = $this->getAppliedMigrations();
        $allFiles = glob($this->migrationsDir . '/*.sql');
        sort($allFiles);

        $pending = [];
        foreach ($allFiles as $filePath) {
            $filename = basename($filePath);
            if (!in_array($filename, $applied, true)) {
                $pending[] = $filePath;
            }
        }

        if (empty($pending)) {
            echo " [OK] Database is up to date. Nothing to migrate.\n\n";
            return;
        }

        $nextBatch = $this->getNextBatchNumber();
        echo "Found " . count($pending) . " pending migration(s) for Batch #{$nextBatch}:\n";

        foreach ($pending as $filePath) {
            $filename = basename($filePath);
            echo " -> Migrating: {$filename} ... ";

            $sql = file_get_contents($filePath);
            if ($sql === false || trim($sql) === '') {
                echo "[SKIPPED - Empty file]\n";
                continue;
            }

            try {
                // MySQL automatically issues implicit commit for DDL (CREATE/ALTER TABLE)
                $this->pdo->exec($sql);

                $stmt = $this->pdo->prepare("INSERT INTO `migrations` (`migration`, `batch`) VALUES (?, ?)");
                $stmt->execute([$filename, $nextBatch]);

                echo "[DONE]\n";
            } catch (\Throwable $e) {
                echo "[FAILED]\n";
                echo "\nERROR in {$filename}: " . $e->getMessage() . "\n";
                exit(1);
            }
        }

        echo "\n [SUCCESS] All pending migrations executed successfully!\n\n";
    }

    private function showStatus(): void
    {
        echo "======================================================\n";
        echo "              MIGRATION STATUS REPORT                 \n";
        echo "======================================================\n\n";

        $appliedMap = [];
        $stmt = $this->pdo->query("SELECT `migration`, `batch`, `executed_at` FROM `migrations` ORDER BY `id` ASC");
        while ($row = $stmt->fetch()) {
            $appliedMap[$row['migration']] = $row;
        }

        $allFiles = glob($this->migrationsDir . '/*.sql');
        sort($allFiles);

        printf("%-45s | %-8s | %-6s | %-19s\n", "Migration File", "Status", "Batch", "Executed At");
        echo str_repeat('-', 85) . "\n";

        foreach ($allFiles as $filePath) {
            $filename = basename($filePath);
            if (isset($appliedMap[$filename])) {
                $info = $appliedMap[$filename];
                printf("%-45s | %-8s | %-6s | %-19s\n", $filename, "APPLIED", $info['batch'], $info['executed_at']);
            } else {
                printf("%-45s | %-8s | %-6s | %-19s\n", $filename, "PENDING", "-", "-");
            }
        }
        echo "\n";
    }

    private function getAppliedMigrations(): array
    {
        $stmt = $this->pdo->query("SELECT `migration` FROM `migrations`");
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    private function getNextBatchNumber(): int
    {
        $stmt = $this->pdo->query("SELECT MAX(`batch`) FROM `migrations`");
        $max = (int)$stmt->fetchColumn();
        return $max + 1;
    }
}

// Execute Runner
$runner = new MigrationRunner();
$runner->run($argv ?? []);
