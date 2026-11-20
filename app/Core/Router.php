<?php
/**
 * FINORA - Fast Parameterized HTTP Router
 */

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middlewares): void
    {
        $cleanPath = '/' . trim($path, '/');
        if ($cleanPath !== '/' && str_ends_with($cleanPath, '/')) {
            $cleanPath = rtrim($cleanPath, '/');
        }

        // Convert {param} placeholders into regex captures
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $cleanPath);
        $regex = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $cleanPath,
            'regex' => $regex,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                // Extract named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Execute middlewares
                foreach ($route['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $middleware->handle($request);
                }

                // Execute Handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, array_values($params));
                    return;
                }

                if (is_array($handler)) {
                    [$controllerClass, $action] = $handler;
                    $controller = new $controllerClass();
                    $refMethod = new \ReflectionMethod($controller, $action);
                    $args = [];
                    $remainingParams = array_values($params);

                    foreach ($refMethod->getParameters() as $param) {
                        $type = $param->getType();
                        $typeName = ($type instanceof \ReflectionNamedType) ? $type->getName() : '';
                        $name = $param->getName();

                        if ($typeName === Request::class || $name === 'request') {
                            $args[] = $request;
                        } elseif (array_key_exists($name, $params)) {
                            $args[] = $params[$name];
                        } elseif (!empty($remainingParams)) {
                            $args[] = array_shift($remainingParams);
                        } elseif ($param->isDefaultValueAvailable()) {
                            $args[] = $param->getDefaultValue();
                        } else {
                            $args[] = null;
                        }
                    }

                    $refMethod->invokeArgs($controller, $args);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        View::render('errors.404', ['title' => 'Page Not Found - FINORA'], 'none');
        exit;
    }
}
