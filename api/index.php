<?php
/**
 * Vercel entrypoint — routes all requests through Laravel's public/index.php
 */
$publicDir = __DIR__ . '/../public';

// Serve static files directly (CSS, JS, images, fonts)
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = $publicDir . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

chdir($publicDir);
require $publicDir . '/index.php';
