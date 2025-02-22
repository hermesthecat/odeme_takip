<?php
header('Content-Type: text/html');

// Sadece development ortamında erişime izin ver
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    header('HTTP/1.1 403 Forbidden');
    die('Bu sayfaya erişim kısıtlanmıştır.');
}

// Swagger UI HTML'ini serve et
readfile(__DIR__ . '/swagger-ui.html');
