<?php

// Vercel static file handler for Laravel
$publicPath = __DIR__ . '/../public';
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Check if requested file exists in public/
$filePath = realpath($publicPath . $requestUri);

// Security: ensure path is within public/ directory (prevent directory traversal)
if ($filePath && str_starts_with($filePath, realpath($publicPath) . DIRECTORY_SEPARATOR)) {
    if (is_file($filePath)) {
        // Serve the static file
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
        ];
        
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        
        // Cache static assets (1 year for build/, 1 hour for others)
        $cacheTime = str_contains($requestUri, '/build/') ? 31536000 : 3600;
        header('Cache-Control: public, max-age=' . $cacheTime);
        
        readfile($filePath);
        exit;
    }
}

// If not a static file, bootstrap Laravel
require __DIR__ . '/../public/index.php';
