<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. Rutas API → van al backend
if (strpos($uri, '/api/') === 0) {
    require __DIR__ . '/api/index.php';
    exit;
}

// 2. Archivos estáticos (css, js, imágenes)
$staticPath = __DIR__ . '/public' . $uri;
if (preg_match('#^/(css|js|images?|favicon\.ico)#', $uri) && is_file($staticPath)) {
    $ext = pathinfo($staticPath, PATHINFO_EXTENSION);
    $types = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg'  => 'image/svg+xml',
        'gif'  => 'image/gif',
        'ico'  => 'image/x-icon',
        'html' => 'text/html'
    ];
    if (isset($types[$ext])) {
        header('Content-Type: ' . $types[$ext]);
    }
    readfile($staticPath);
    exit;
}

// 3. Si no es API ni archivo estático → carga el index.html (SPA o frontend)
require __DIR__ . '/public/index.html';
