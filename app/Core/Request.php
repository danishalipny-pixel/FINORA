<?php
/**
 * FINORA - HTTP Request Handler
 */

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;
    private ?array $json = null;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;

        // Parse JSON payload if sent with application/json
        $contentType = $this->header('Content-Type') ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $this->json = $decoded;
                }
            }
        }
    }

    public function method(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        // Support method spoofing via _method input
        if ($method === 'POST' && isset($this->post['_method'])) {
            return strtoupper((string)$this->post['_method']);
        }
        return $method;
    }

    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        $clean = rtrim($path ?? '/', '/');
        return $clean === '' ? '/' : $clean;
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isAjax(): bool
    {
        return (
            ($this->header('X-Requested-With') === 'XMLHttpRequest') ||
            str_contains($this->header('Accept') ?? '', 'application/json')
        );
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if ($this->json !== null && array_key_exists($key, $this->json)) {
            return $this->json[$key];
        }
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }
        if (array_key_exists($key, $this->get)) {
            return $this->get[$key];
        }
        return $default;
    }

    public function all(): array
    {
        $all = array_merge($this->get, $this->post);
        if ($this->json !== null) {
            $all = array_merge($all, $this->json);
        }
        return $all;
    }

    public function postData(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->json ?? $this->post;
        }
        if ($this->json !== null && array_key_exists($key, $this->json)) {
            return $this->json[$key];
        }
        return $this->post[$key] ?? $default;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $formatted = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        if (isset($this->server[$formatted])) {
            return $this->server[$formatted];
        }
        if (isset($this->server[$key])) {
            return $this->server[$key];
        }
        // Direct header search case-insensitive
        foreach ($this->server as $k => $v) {
            if (str_starts_with($k, 'HTTP_')) {
                $h = strtolower(str_replace('_', '-', substr($k, 5)));
                if ($h === strtolower($key)) {
                    return $v;
                }
            }
        }
        return $default;
    }

    public function csrfToken(): ?string
    {
        return $this->input('_csrf_token') ?? $this->header('X-CSRF-TOKEN');
    }

    public function ip(): string
    {
        return (string)(
            $this->server['HTTP_CLIENT_IP'] ??
            $this->server['HTTP_X_FORWARDED_FOR'] ??
            $this->server['REMOTE_ADDR'] ??
            '127.0.0.1'
        );
    }
}
