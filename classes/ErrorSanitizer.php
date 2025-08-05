<?php

/**
 * Error Message Sanitizer
 * Production-safe error message handling with information disclosure prevention
 */
class ErrorSanitizer
{
    private static $instance = null;
    private $isDevelopment = false;
    private $genericMessages = [];
    private $sensitivePatterns = [];

    private function __construct()
    {
        $this->isDevelopment = (getenv('APP_ENV') === 'development');
        $this->initializeGenericMessages();
        $this->initializeSensitivePatterns();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize generic error messages for production
     */
    private function initializeGenericMessages()
    {
        $this->genericMessages = [
            // Database errors
            'database_error' => 'A system error occurred. Please try again later.',
            'connection_failed' => 'Service temporarily unavailable. Please try again later.',
            'query_failed' => 'Unable to process your request. Please try again.',
            'constraint_violation' => 'Invalid data provided. Please check your input.',
            
            // File system errors
            'file_not_found' => 'Requested resource not found.',
            'file_permission' => 'Unable to access the requested resource.',
            'upload_failed' => 'File upload failed. Please try again.',
            'invalid_file_type' => 'Invalid file type provided.',
            
            // Authentication errors
            'auth_failed' => 'Authentication failed. Please check your credentials.',
            'session_expired' => 'Your session has expired. Please log in again.',
            'permission_denied' => 'You do not have permission to perform this action.',
            'account_locked' => 'Account temporarily locked. Please try again later.',
            
            // Validation errors
            'validation_failed' => 'Please check the information you provided.',
            'invalid_input' => 'Invalid input provided. Please correct and try again.',
            'missing_required' => 'Required information is missing.',
            
            // External API errors
            'api_error' => 'External service error. Please try again later.',
            'rate_limit_exceeded' => 'Too many requests. Please wait and try again.',
            'service_unavailable' => 'Service temporarily unavailable.',
            
            // Generic fallback
            'generic_error' => 'An error occurred. Please try again later.'
        ];
    }

    /**
     * Initialize patterns that should trigger sanitization
     */
    private function initializeSensitivePatterns()
    {
        $this->sensitivePatterns = [
            // Database patterns
            '/mysql|mysqli|pdo|sql|database/i' => 'database_error',
            '/table.*doesn\'t exist/i' => 'database_error',
            '/column.*unknown/i' => 'database_error',
            '/constraint.*failed/i' => 'constraint_violation',
            '/duplicate entry/i' => 'constraint_violation',
            '/connection.*refused/i' => 'connection_failed',
            
            // File system patterns
            '/file.*not.*found/i' => 'file_not_found',
            '/permission.*denied/i' => 'file_permission',
            '/failed to open stream/i' => 'file_permission',
            '/upload.*failed/i' => 'upload_failed',
            '/invalid.*mime.*type/i' => 'invalid_file_type',
            
            // Path disclosure patterns
            '/\/var\/www|\/home\/|\/usr\/|c:\\\\|d:\\\\/i' => 'generic_error',
            '/\.php.*line.*\d+/i' => 'generic_error',
            '/stack trace|backtrace/i' => 'generic_error',
            
            // Authentication patterns
            '/invalid.*credentials|login.*failed/i' => 'auth_failed',
            '/session.*expired|session.*invalid/i' => 'session_expired',
            '/unauthorized|access.*denied/i' => 'permission_denied',
            '/too.*many.*attempts|rate.*limit/i' => 'account_locked',
            
            // Validation patterns
            '/validation.*error|invalid.*input/i' => 'validation_failed',
            '/required.*field|missing.*parameter/i' => 'missing_required',
            
            // External API patterns
            '/curl.*error|http.*request.*failed/i' => 'api_error',
            '/api.*limit|quota.*exceeded/i' => 'rate_limit_exceeded',
            '/service.*unavailable|timeout/i' => 'service_unavailable'
        ];
    }

    /**
     * Sanitize error message for production use
     * @param string $message Original error message
     * @param string $context Optional context for better error categorization
     * @return string Sanitized error message
     */
    public function sanitize($message, $context = null)
    {
        // In development, return original message
        if ($this->isDevelopment) {
            return $message;
        }

        // Convert to lowercase for pattern matching
        $lowerMessage = strtolower($message);

        // Check for sensitive patterns
        foreach ($this->sensitivePatterns as $pattern => $messageKey) {
            if (preg_match($pattern, $lowerMessage)) {
                return $this->genericMessages[$messageKey] ?? $this->genericMessages['generic_error'];
            }
        }

        // Context-based sanitization
        if ($context) {
            switch (strtolower($context)) {
                case 'database':
                case 'db':
                    return $this->genericMessages['database_error'];
                    
                case 'file':
                case 'upload':
                    return $this->genericMessages['upload_failed'];
                    
                case 'auth':
                case 'login':
                    return $this->genericMessages['auth_failed'];
                    
                case 'validation':
                    return $this->genericMessages['validation_failed'];
                    
                case 'api':
                case 'external':
                    return $this->genericMessages['api_error'];
            }
        }

        // If message seems safe and short, keep it
        if (strlen($message) < 100 && !$this->containsSensitiveInfo($message)) {
            return $message;
        }

        // Fallback to generic error
        return $this->genericMessages['generic_error'];
    }

    /**
     * Check if message contains sensitive information
     */
    private function containsSensitiveInfo($message)
    {
        $sensitiveIndicators = [
            '/\/[a-z]+\/[a-z]+\//',  // Unix paths
            '/[a-z]:\\\\[a-z]+\\\\/i',  // Windows paths
            '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',  // IP addresses
            '/password|secret|token|key/i',  // Sensitive keywords
            '/mysql|database|sql/i',  // Database info
            '/line \d+/i',  // Stack trace info
            '/function [a-z_]+\(/i'  // Function names
        ];

        foreach ($sensitiveIndicators as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create sanitized API error response
     */
    public function createApiErrorResponse($message, $errorCode = 'GENERIC_ERROR', $httpCode = 400, $context = null)
    {
        $sanitizedMessage = $this->sanitize($message, $context);
        
        $response = [
            'status' => 'error',
            'message' => $sanitizedMessage,
            'error_code' => $errorCode
        ];

        // Add debug info only in development
        if ($this->isDevelopment && $message !== $sanitizedMessage) {
            $response['debug'] = [
                'original_message' => $message,
                'sanitized' => true
            ];
        }

        header('Content-Type: application/json');
        http_response_code($httpCode);

        return json_encode($response);
    }

    /**
     * Log error securely (always log original, return sanitized)
     */
    public function logAndSanitize($message, $context = null, $level = 'error')
    {
        // Always log the original message for debugging
        $logMessage = "[{$level}] " . ($context ? "[$context] " : "") . $message;
        error_log($logMessage);

        // Return sanitized message for user display
        return $this->sanitize($message, $context);
    }

    /**
     * Handle exception with sanitization
     */
    public function handleException($exception, $context = null)
    {
        $originalMessage = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();

        // Log full exception details
        $logMessage = "Exception: $originalMessage in $file:$line";
        if ($context) {
            $logMessage = "[$context] $logMessage";
        }
        error_log($logMessage);

        // Return sanitized message
        return $this->sanitize($originalMessage, $context);
    }

    /**
     * Get appropriate HTTP status code for error type
     */
    public function getHttpStatusCode($message, $context = null)
    {
        $lowerMessage = strtolower($message);

        // Authentication/Authorization errors
        if (preg_match('/unauthorized|permission|access.*denied/i', $lowerMessage)) {
            return 403;
        }

        if (preg_match('/session.*expired|login.*required/i', $lowerMessage)) {
            return 401;
        }

        // Not found errors
        if (preg_match('/not.*found|doesn\'t.*exist/i', $lowerMessage)) {
            return 404;
        }

        // Validation errors
        if (preg_match('/validation|invalid.*input|required.*field/i', $lowerMessage)) {
            return 422;
        }

        // Rate limiting
        if (preg_match('/rate.*limit|too.*many.*requests/i', $lowerMessage)) {
            return 429;
        }

        // Server errors
        if (preg_match('/database|connection|internal.*error/i', $lowerMessage)) {
            return 500;
        }

        // Service unavailable
        if (preg_match('/unavailable|timeout|maintenance/i', $lowerMessage)) {
            return 503;
        }

        // Default to 400 Bad Request
        return 400;
    }
}