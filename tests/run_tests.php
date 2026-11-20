<?php
/**
 * FINORA - Master Automated Test Runner
 *
 * Runs full PHP syntax linting, database integrity checks, model validations,
 * and executes Unit, Integration, and Feature test suites.
 *
 * Usage:
 *   php tests/run_tests.php
 *
 * @package Finora\Tests
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Router;
use App\Core\Validator;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Notification;
use App\Models\SavingsGoal;
use App\Models\SavingsContribution;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FinancialAnalyticsService;
use Tests\Unit\ValidatorTest;
use Tests\Unit\AccountBalanceTest;
use Tests\Unit\CsrfTest;
use Tests\Unit\PasswordSecurityTest;
use Tests\Integration\TransactionRepositoryTest;
use Tests\Integration\BudgetCalculationTest;
use Tests\Integration\SavingsContributionTest;
use Tests\Feature\AuthenticationFlowTest;
use Tests\Feature\DashboardAnalyticsTest;
use Tests\Feature\ReportGenerationTest;
use Tests\Feature\HttpRoutingTest;

class FinoraMasterTestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];
    private float $startTime;

    public function run(): void
    {
        $this->startTime = microtime(true);

        echo "======================================================\n";
        echo "       FINORA FINANCIAL SUITE - AUTOMATED TESTS       \n";
        echo "======================================================\n\n";

        // 1. PHP Syntax Linting
        $this->runSyntaxLinting();

        // 2. Unit Tests
        $this->runSuite('UNIT TEST SUITE', [
            ValidatorTest::class,
            AccountBalanceTest::class,
            CsrfTest::class,
            PasswordSecurityTest::class,
        ]);

        // 3. Integration Tests
        $this->runSuite('INTEGRATION TEST SUITE', [
            TransactionRepositoryTest::class,
            BudgetCalculationTest::class,
            SavingsContributionTest::class,
        ]);

        // 4. Feature Tests
        $this->runSuite('FEATURE TEST SUITE', [
            AuthenticationFlowTest::class,
            DashboardAnalyticsTest::class,
            ReportGenerationTest::class,
            HttpRoutingTest::class,
        ]);

        // 5. System Schema & Integrity Tests
        $this->runSystemIntegrityTests();

        $duration = round(microtime(true) - $this->startTime, 3);

        echo "\n======================================================\n";
        echo "TEST SUITE SUMMARY:\n";
        echo "Passed:   " . $this->passed . "\n";
        echo "Failed:   " . $this->failed . "\n";
        echo "Duration: {$duration}s\n";
        echo "======================================================\n";

        if ($this->failed > 0) {
            echo "\nFailures Identified:\n";
            foreach ($this->failures as $f) {
                echo "  [FAIL] " . $f . "\n";
            }
            exit(1);
        } else {
            echo "\nALL TESTS PASSED SUCCESSFULLY! (100% Verification Complete)\n\n";
            exit(0);
        }
    }

    private function assert(bool $condition, string $testName): void
    {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] {$testName}\n";
        } else {
            $this->failed++;
            $this->failures[] = $testName;
            echo "  [FAIL] {$testName}\n";
        }
    }

    private function runSuite(string $title, array $classes): void
    {
        echo "------------------------------------------------------\n";
        echo "Running: {$title}\n";
        echo "------------------------------------------------------\n";

        foreach ($classes as $class) {
            $shortName = (new \ReflectionClass($class))->getShortName();
            echo " -> {$shortName}:\n";
            $suiteInstance = new $class();
            $results = $suiteInstance->run();
            foreach ($results as $res) {
                $this->assert($res['pass'], $res['name']);
            }
        }
        echo "\n";
    }

    private function runSyntaxLinting(): void
    {
        echo "Running: PHP Syntax Linting across all project files...\n";
        $files = $this->getPhpFiles(dirname(__DIR__));
        $allValid = true;

        foreach ($files as $file) {
            $output = [];
            $cmd = 'php -l "' . str_replace('"', '\"', $file) . '" 2>&1';
            exec($cmd, $output, $returnCode);
            if ($returnCode !== 0) {
                $allValid = false;
                $this->assert(false, "Syntax check on " . basename($file) . " failed: " . implode(" ", $output));
            }
        }

        if ($allValid) {
            $this->assert(true, "All " . count($files) . " PHP files have valid syntax without errors.");
        }
        echo "\n";
    }

    private function runSystemIntegrityTests(): void
    {
        echo "------------------------------------------------------\n";
        echo "Running: SYSTEM INTEGRITY & DATABASE VALIDATION\n";
        echo "------------------------------------------------------\n";

        try {
            $pdo = Database::connection();
            $this->assert(true, "Connected to active MySQL database: finora_db");

            $tables = [
                'users', 'user_settings', 'accounts', 'categories',
                'transactions', 'budgets', 'savings_goals',
                'savings_contributions', 'notifications', 'password_resets',
                'migrations'
            ];

            foreach ($tables as $t) {
                $stmt = $pdo->query("SHOW TABLES LIKE '{$t}'");
                $exists = (bool)$stmt->fetch();
                $this->assert($exists, "Database table exists: {$t}");
            }
        } catch (\Throwable $e) {
            $this->assert(false, "Database connection failed: " . $e->getMessage());
        }
    }

    private function getPhpFiles(string $dir): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $path = $file->getPathname();
                // Exclude vendor or cache if present
                if (!str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) &&
                    !str_contains($path, DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache')) {
                    $files[] = $path;
                }
            }
        }

        return $files;
    }
}

// Execute Runner
$runner = new FinoraMasterTestRunner();
$runner->run();
