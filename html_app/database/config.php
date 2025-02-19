<?php
// Database configuration
return [
    'host' => 'localhost',
    'dbname' => 'odeme_takip',
    'username' => 'root',
    'password' => 'root', // Change this to your actual password
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        // Display all errors in development
        PDO::ATTR_PERSISTENT => true,
        // Enable debugging in development
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        // Report all errors
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ],
    // Development mode - set to false in production
    'debug' => true
];
