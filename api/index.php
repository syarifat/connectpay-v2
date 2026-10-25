<?php

// Vercel PHP runtime fix: ensure correct working directory
chdir(__DIR__ . '/..');

// Force proper headers for Laravel on Vercel
header_remove('Content-Type');
header('Content-Type: text/html; charset=UTF-8');

// Require Laravel entry point
require __DIR__ . '/../public/index.php';
