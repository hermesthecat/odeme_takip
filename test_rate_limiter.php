<?php

/**
 * Rate Limiter Test Script
 * Test the MySQL-based rate limiting system
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/RateLimiter.php';

// Set fake session data for testing
$_SESSION['user_id'] = 1;

echo "<h2>Rate Limiter Test</h2>\n";

try {
    $rateLimiter = RateLimiter::getInstance();
    
    // Test different scenarios
    $testCases = [
        ['endpoint' => 'api.php', 'identifier' => '127.0.0.1', 'type' => 'ip'],
        ['endpoint' => 'login', 'identifier' => '127.0.0.1', 'type' => 'ip'],
        ['endpoint' => 'file_upload', 'identifier' => '127.0.0.1:1', 'type' => 'user'],
        ['endpoint' => 'exchange_rate_refresh', 'identifier' => '127.0.0.1:1', 'type' => 'user']
    ];
    
    foreach ($testCases as $i => $test) {
        echo "<h3>Test " . ($i + 1) . ": {$test['endpoint']} ({$test['type']})</h3>\n";
        
        // Make 3 requests to see rate limiting in action
        for ($j = 1; $j <= 3; $j++) {
            $result = $rateLimiter->checkLimit($test['endpoint'], $test['identifier'], $test['type']);
            
            echo "<p>Request $j:</p>\n";
            echo "<ul>\n";
            echo "<li>Allowed: " . ($result['allowed'] ? 'YES' : 'NO') . "</li>\n";
            echo "<li>Remaining: {$result['remaining']}</li>\n";
            echo "<li>Limit: {$result['limit']}</li>\n";
            echo "<li>Reset Time: " . date('Y-m-d H:i:s', $result['reset_time']) . "</li>\n";
            echo "</ul>\n";
            
            if (!$result['allowed']) {
                echo "<p><strong>Rate limit exceeded!</strong></p>\n";
                break;
            }
        }
        
        echo "<hr>\n";
    }
    
    // Test violation logging
    echo "<h3>Recent Violations</h3>\n";
    $stats = $rateLimiter->getStats();
    
    if (empty($stats)) {
        echo "<p>No violations in the last 24 hours.</p>\n";
    } else {
        echo "<table border='1'>\n";
        echo "<tr><th>Endpoint</th><th>Violations</th><th>Last Violation</th></tr>\n";
        foreach ($stats as $stat) {
            echo "<tr>\n";
            echo "<td>{$stat['endpoint']}</td>\n";
            echo "<td>{$stat['violation_count']}</td>\n";
            echo "<td>{$stat['last_violation']}</td>\n";
            echo "</tr>\n";
        }
        echo "</table>\n";
    }
    
    echo "<h3>Test Completed Successfully!</h3>\n";
    
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><strong>Trace:</strong></p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}