<?php

/**
 * Rate Limit Cleanup Script
 * Expired rate limit records and old violation logs
 * 
 * Usage:
 * - Run via cron job: 0 * * * * /usr/bin/php /path/to/cleanup_rate_limits.php
 * - Manual execution: php cleanup_rate_limits.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/RateLimiter.php';

// CLI-only script
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be executed from command line');
}

function cleanupRateLimits() {
    try {
        $rateLimiter = RateLimiter::getInstance();
        
        echo date('Y-m-d H:i:s') . " - Starting rate limit cleanup...\n";
        
        // Cleanup expired records
        $rateLimiter->cleanup();
        
        // Get cleanup statistics
        global $pdo;
        
        // Count active limits
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM rate_limits");
        $activeLimits = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count violations in last 24 hours
        $stmt = $pdo->query("
            SELECT COUNT(*) as count 
            FROM rate_limit_violations 
            WHERE violation_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $violations24h = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count violations in last 7 days
        $stmt = $pdo->query("
            SELECT COUNT(*) as count 
            FROM rate_limit_violations 
            WHERE violation_time > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $violations7d = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Top violated endpoints in last 24h
        $stmt = $pdo->query("
            SELECT endpoint, COUNT(*) as violation_count
            FROM rate_limit_violations 
            WHERE violation_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY endpoint
            ORDER BY violation_count DESC
            LIMIT 5
        ");
        $topViolations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo date('Y-m-d H:i:s') . " - Cleanup completed successfully\n";
        echo "Statistics:\n";
        echo "- Active rate limits: $activeLimits\n";
        echo "- Violations (24h): $violations24h\n";
        echo "- Violations (7d): $violations7d\n";
        
        if (!empty($topViolations)) {
            echo "Top violated endpoints (24h):\n";
            foreach ($topViolations as $violation) {
                echo "  - {$violation['endpoint']}: {$violation['violation_count']} violations\n";
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        echo date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
        return false;
    }
}

// Auto-cleanup on script execution
if (cleanupRateLimits()) {
    exit(0);
} else {
    exit(1);
}