<?php

chdir(__DIR__ . '/..');

// Vercel: writable paths to /tmp
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
@mkdir('/tmp/storage/framework/views', 0755, true);
@mkdir('/tmp/storage/logs', 0755, true);
@mkdir('/tmp/bootstrap/cache', 0755, true);

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp/storage');
$app->useCachePath('/tmp/bootstrap/cache');

$app->handleRequest(Illuminate\Http\Request::capture())->send();
