<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Force load the single .env file for this project to avoid .env.* overrides
$__env_base = dirname(__DIR__);
if (file_exists($__env_base.'/.env')) {
    try {
        \Dotenv\Dotenv::createImmutable($__env_base, '.env')->safeLoad();
    } catch (\Throwable $__e) {
        // Ignore dotenv failures here; bootstrap will handle missing envs
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
