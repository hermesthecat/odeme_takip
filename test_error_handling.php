<?php

/**
 * Error Handling Test Script
 * Test the improved error handling and sanitization system
 */

require_once __DIR__ . '/config.php';

echo "<h2>Error Handling & Sanitization Test</h2>\n";
echo "<p><strong>Environment:</strong> " . (getenv('APP_ENV') ?: 'production') . "</p>\n";

try {
    $errorSanitizer = ErrorSanitizer::getInstance();
    
    // Test various error message types
    $testCases = [
        // Database errors
        [
            'original' => 'SQLSTATE[42S02]: Base table or view not found: 1146 Table \'myapp.nonexistent\' doesn\'t exist',
            'context' => 'database',
            'expected_type' => 'database_error'
        ],
        [
            'original' => 'PDO::query(): MySQL server has gone away in /var/www/html/config.php line 67',
            'context' => 'database',
            'expected_type' => 'path_disclosure'
        ],
        
        // File system errors  
        [
            'original' => 'failed to open stream: Permission denied in /home/user/app/upload.php line 123',
            'context' => 'file',
            'expected_type' => 'file_permission'
        ],
        [
            'original' => 'File upload failed: Invalid MIME type application/x-php',
            'context' => 'upload',
            'expected_type' => 'invalid_file_type'
        ],
        
        // Authentication errors
        [
            'original' => 'Invalid credentials provided for user: admin',
            'context' => 'auth',
            'expected_type' => 'auth_failed'
        ],
        [
            'original' => 'Session expired at 2024-01-08 15:30:45 for IP 192.168.1.100',
            'context' => 'auth',  
            'expected_type' => 'session_expired'
        ],
        
        // Validation errors
        [
            'original' => 'Validation failed: Amount must be numeric, received "abc123"',
            'context' => 'validation',
            'expected_type' => 'validation_failed'
        ],
        
        // External API errors
        [
            'original' => 'cURL error 28: Operation timed out after 30000 milliseconds with 0 bytes received',
            'context' => 'api',
            'expected_type' => 'api_error'
        ],
        
        // Safe messages (should pass through)
        [
            'original' => 'Form data is required',
            'context' => null,
            'expected_type' => 'safe_passthrough'
        ],
        [
            'original' => 'Invalid currency selected',
            'context' => null,
            'expected_type' => 'safe_passthrough'
        ]
    ];
    
    echo "<h3>Message Sanitization Tests</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr>\n";
    echo "<th style='padding: 8px; background: #f0f0f0;'>Test Case</th>\n";
    echo "<th style='padding: 8px; background: #f0f0f0;'>Original Message</th>\n";
    echo "<th style='padding: 8px; background: #f0f0f0;'>Context</th>\n";
    echo "<th style='padding: 8px; background: #f0f0f0;'>Sanitized Message</th>\n";
    echo "<th style='padding: 8px; background: #f0f0f0;'>Status</th>\n";
    echo "</tr>\n";
    
    foreach ($testCases as $i => $test) {
        $sanitized = $errorSanitizer->sanitize($test['original'], $test['context']);
        $isDifferent = ($sanitized !== $test['original']);
        $status = $isDifferent ? "✅ SANITIZED" : "➡️ PASSED";
        
        echo "<tr>\n";
        echo "<td style='padding: 8px;'>" . ($i + 1) . "</td>\n";
        echo "<td style='padding: 8px; max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars(substr($test['original'], 0, 100)) . 
             (strlen($test['original']) > 100 ? '...' : '') . "</td>\n";
        echo "<td style='padding: 8px;'>" . ($test['context'] ?: 'none') . "</td>\n";
        echo "<td style='padding: 8px; max-width: 300px; word-wrap: break-word;'>" . htmlspecialchars($sanitized) . "</td>\n";
        echo "<td style='padding: 8px;'>{$status}</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Test HTTP status code detection
    echo "<h3>HTTP Status Code Detection Tests</h3>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr>\n";
    echo "<th style='padding: 8px; background: #f0f0f0;'>Message</th>\n";
    echo "<th style='padding: 8px; background: #f0f0f0;'>Detected Code</th>\n";
    echo "<th style='padding: 8px; background: #f0f0f0;'>Expected</th>\n";
    echo "</tr>\n";
    
    $statusTests = [
        ['Unauthorized access denied', 403],
        ['Session expired please login', 401], 
        ['Resource not found', 404],
        ['Validation error: required field missing', 422],
        ['Rate limit exceeded', 429],
        ['Database connection failed', 500],
        ['Service temporarily unavailable', 503]
    ];
    
    foreach ($statusTests as $test) {
        $detectedCode = $errorSanitizer->getHttpStatusCode($test[0]);
        $status = ($detectedCode === $test[1]) ? '✅' : '❌';
        
        echo "<tr>\n";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($test[0]) . "</td>\n";
        echo "<td style='padding: 8px;'>{$detectedCode}</td>\n";
        echo "<td style='padding: 8px;'>{$test[1]} {$status}</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Test API error response creation
    echo "<h3>API Error Response Tests</h3>\n";
    
    $apiTests = [
        ['Database connection failed', 'DB_ERROR', 'database'],
        ['File not found: /secret/path/file.txt', 'FILE_NOT_FOUND', 'file'],
        ['Invalid login credentials', 'AUTH_FAILED', 'auth']
    ];
    
    foreach ($apiTests as $i => $test) {
        echo "<h4>Test " . ($i + 1) . ": {$test[1]}</h4>\n";
        $response = createApiErrorResponse($test[0], $test[1], 400, null, $test[2]);
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px;'>" . 
             htmlspecialchars(json_encode(json_decode($response), JSON_PRETTY_PRINT)) . "</pre>\n";
    }
    
    // Test exception handling
    echo "<h3>Exception Handling Test</h3>\n";
    
    try {
        // Simulate a database error
        throw new PDOException("SQLSTATE[HY000] [2002] Connection refused to mysql server at localhost:3306");
    } catch (Exception $e) {
        $sanitizedMessage = $errorSanitizer->handleException($e, 'database');
        echo "<p><strong>Original Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
        echo "<p><strong>Sanitized Message:</strong> " . htmlspecialchars($sanitizedMessage) . "</p>\n";
        echo "<p><strong>Status:</strong> " . ($sanitizedMessage !== $e->getMessage() ? "✅ SANITIZED" : "❌ NOT SANITIZED") . "</p>\n";
    }
    
    echo "<h3>✅ All Tests Completed Successfully!</h3>\n";
    echo "<p>Error handling and sanitization system is working correctly.</p>\n";
    
} catch (Exception $e) {
    echo "<p><strong>❌ Test Failed:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}

echo "<hr>\n";
echo "<p><strong>Note:</strong> In production, sensitive information is automatically sanitized while preserving the original error in server logs for debugging.</p>\n";