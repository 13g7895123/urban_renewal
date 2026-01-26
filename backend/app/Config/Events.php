<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

/*
 * --------------------------------------------------------------------
 * Dependency Validation
 * --------------------------------------------------------------------
 * Validates that critical composer packages are installed.
 * This prevents cryptic "class not found" errors in production.
 */
Events::on('pre_system', static function (): void {
    // Skip validation in testing environment
    if (ENVIRONMENT === 'testing') {
        return;
    }

    // Only run validation once per request
    static $validated = false;
    if ($validated) {
        return;
    }
    $validated = true;

    // List of critical classes that must be available
    $requiredClasses = [
        'PhpOffice\PhpWord\TemplateProcessor' => 'phpoffice/phpword',
        'PhpOffice\PhpSpreadsheet\Spreadsheet' => 'phpoffice/phpspreadsheet',
        'Firebase\JWT\JWT' => 'firebase/php-jwt',
    ];

    $missingPackages = [];

    foreach ($requiredClasses as $className => $packageName) {
        if (!class_exists($className)) {
            $missingPackages[] = $packageName;
        }
    }

    if (!empty($missingPackages)) {
        $message = "⚠️ Critical dependencies missing:\n\n";
        $message .= "The following composer packages are not installed:\n";
        foreach ($missingPackages as $package) {
            $message .= "  • $package\n";
        }
        $message .= "\nPlease run: composer install\n";
        $message .= "Or restart the Docker container to auto-install dependencies.";

        // Log the error
        log_message('critical', 'Missing required packages: ' . implode(', ', $missingPackages));

        // In development, show detailed error
        if (ENVIRONMENT === 'development') {
            throw new \RuntimeException($message);
        }

        // In production, log and continue (allow health checks to work)
        log_message('error', $message);
    }
});

/*
 * --------------------------------------------------------------------
 * Error Logging System
 * --------------------------------------------------------------------
 * Automatically log exceptions and errors to database for debugging.
 */
Events::on('pre_system', static function (): void {
    // Skip in testing environment
    if (ENVIRONMENT === 'testing') {
        return;
    }

    // Register global error and exception handlers
    try {
        $errorLogger = new \App\Libraries\ErrorLogger();
        $errorLogger->register();
    } catch (\Throwable $e) {
        // If error logger fails to initialize, just log it
        log_message('error', 'Failed to initialize error logger: ' . $e->getMessage());
    }
});
