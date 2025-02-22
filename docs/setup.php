<?php
// Docs dizinini web kökünde erişilebilir yap
$sourceDir = __DIR__;
$targetDir = $_SERVER['DOCUMENT_ROOT'] . '/docs';

if (!file_exists($targetDir)) {
    if (PHP_OS_FAMILY === 'Windows') {
        exec("mklink /D \"$targetDir\" \"$sourceDir\"");
    } else {
        symlink($sourceDir, $targetDir);
    }
    echo "Docs dizini başarıyla bağlandı.\n";
} else {
    echo "Docs dizini zaten mevcut.\n";
}

// Gerekli dizin izinlerini ayarla
if (PHP_OS_FAMILY !== 'Windows') {
    chmod($sourceDir, 0755);
    chmod($targetDir, 0755);
    echo "Dizin izinleri ayarlandı.\n";
}
