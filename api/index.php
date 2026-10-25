<?php

chdir(__DIR__ . '/..');

// Vercel: writable paths to /tmp
$_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
@mkdir('/tmp/storage/framework/views', 0755, true);
@mkdir('/tmp/storage/logs', 0755, true);

// Copy bootstrap cache files to writable location
$cacheFiles = ['packages.php', 'services.php'];
foreach ($cacheFiles as $file) {
    $src = __DIR__ . '/../bootstrap/cache/' . $file;
    $dst = '/tmp/' . $file;
    if (file_exists($src) && !file_exists($dst)) {
        @copy($src, $dst);
    }
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp/storage');

$app->handleRequest(Illuminate\Http\Request::capture())->send();
