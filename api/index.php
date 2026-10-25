<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

chdir(__DIR__ . '/..');

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    $response = $app->handleRequest(Illuminate\Http\Request::capture());
    $response->send();
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<pre>Error: ' . $e->getMessage() . "\n\n" . $e->getTraceAsString() . '</pre>';
}
