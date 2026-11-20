<?php
/**
 * FINORA - Template View Engine & UI Helpers
 */

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    private static string $viewsPath = '';

    public static function init(): void
    {
        if (self::$viewsPath === '') {
            self::$viewsPath = dirname(__DIR__, 2) . '/resources/views';
        }
    }

    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        self::init();

        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("View file not found: {$viewFile}");
        }

        // Global view data
        $auth = Auth::user();
        $settings = Auth::settings();
        $csrfToken = Csrf::getToken();

        $mergedData = array_merge([
            'currentUser' => $auth,
            'userSettings' => $settings,
            'csrfToken' => $csrfToken,
            'flashSuccess' => Session::getFlash('success'),
            'flashError' => Session::getFlash('error'),
            'flashWarning' => Session::getFlash('warning'),
            'flashInfo' => Session::getFlash('info'),
            'validationErrors' => Session::getErrors(),
        ], $data);

        // Extract variables for view
        extract($mergedData, EXTR_SKIP);

        // Capture view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // If no layout requested, output directly
        if ($layout === '' || $layout === 'none') {
            echo $content;
            return;
        }

        // Wrap in layout
        $layoutFile = self::$viewsPath . "/layouts/{$layout}.php";
        if (!file_exists($layoutFile)) {
            throw new RuntimeException("Layout file not found: {$layoutFile}");
        }

        include $layoutFile;
    }
}

