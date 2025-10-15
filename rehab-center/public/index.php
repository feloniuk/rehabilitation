<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Почати буферизацію для Windows
if (PHP_OS_FAMILY === 'Windows') {
    ob_start(function($buffer) {
        // Видалити MadelineProto warning
        $buffer = preg_replace('/WARNING:.*?MadelineProto.*?\n/s', '', $buffer);
        return $buffer;
    });
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());