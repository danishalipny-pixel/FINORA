<?php
/**
 * FINORA - Feature Test: Application Route Registry
 *
 * @package Finora\Tests\Feature
 * @author Danish Ali <https://github.com/danishalipny-pixel>
 */

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Router;

class HttpRoutingTest
{
    public function run(): array
    {
        $results = [];

        $router = new Router();
        $routesFile = dirname(__DIR__, 2) . '/routes/web.php';
        require $routesFile;

        $routes = $router->getRoutes();
        $results[] = [
            'name' => 'Web route registry contains defined application endpoints',
            'pass' => count($routes) >= 17,
        ];

        $registeredKeys = [];
        foreach ($routes as $r) {
            $registeredKeys[] = $r['method'] . ':' . $r['path'];
        }

        $requiredPaths = [
            'GET:/',
            'GET:/login',
            'POST:/login',
            'GET:/register',
            'POST:/register',
            'GET:/dashboard',
            'GET:/transactions',
            'GET:/categories',
            'GET:/budgets',
            'GET:/savings',
            'GET:/accounts',
            'GET:/reports',
            'GET:/notifications',
            'GET:/profile',
            'GET:/settings',
        ];

        foreach ($requiredPaths as $expected) {
            $results[] = [
                'name' => "Route '{$expected}' is correctly registered",
                'pass' => in_array($expected, $registeredKeys, true),
            ];
        }

        return $results;
    }
}
