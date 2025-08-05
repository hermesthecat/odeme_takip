<?php

/**
 * Global Error Handler
 * @author A. Kerem Gök
 * 
 * Bu dosya global error handling sağlar.
 * Production'da user-friendly error messages,
 * development'da detailed error information.
 */

require_once __DIR__ . '/../classes/ErrorSanitizer.php';

/**
 * Global exception handler
 */
function globalExceptionHandler($exception)
{
    $errorSanitizer = ErrorSanitizer::getInstance();
    
    // Get sanitized error message (logs original internally)
    $sanitizedMessage = $errorSanitizer->handleException($exception);
    $appropriateHttpCode = $errorSanitizer->getHttpStatusCode($exception->getMessage());
    
    // Check if this is an AJAX/API request
    $isApiRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    $isApiRequest = $isApiRequest || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false);
    
    if ($isApiRequest) {
        // API/AJAX request - return sanitized JSON error
        header('Content-Type: application/json');
        http_response_code($appropriateHttpCode);
        
        $response = [
            'status' => 'error',
            'message' => $sanitizedMessage,
            'error_code' => 'INTERNAL_SERVER_ERROR'
        ];
        
        // In development, add debug details (already handled by ErrorSanitizer)
        if (getenv('APP_ENV') === 'development') {
            $response['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'http_code' => $appropriateHttpCode
            ];
        }
        
        echo json_encode($response);
    } else {
        // Web request - return HTML error page with sanitized message
        http_response_code($appropriateHttpCode);
        $errorTitle = "System Error";
        $errorMessage = htmlspecialchars($sanitizedMessage);
        
        if (getenv('APP_ENV') === 'development') {
            $errorMessage .= "<br><br><strong>Debug Info:</strong><br>";
            $errorMessage .= "Original Message: " . htmlspecialchars($exception->getMessage()) . "<br>";
            $errorMessage .= "File: " . htmlspecialchars($exception->getFile()) . "<br>";
            $errorMessage .= "Line: " . $exception->getLine() . "<br>";
            $errorMessage .= "HTTP Code: " . $appropriateHttpCode . "<br>";
        }
        
        echo "<!DOCTYPE html>
<html>
<head>
    <title>{$errorTitle}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .error-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error-header { color: #d32f2f; border-bottom: 2px solid #d32f2f; padding-bottom: 10px; margin-bottom: 20px; }
        .error-message { margin: 20px 0; line-height: 1.6; color: #333; }
        .error-actions { margin-top: 30px; }
        .btn { display: inline-block; padding: 10px 20px; background: #1976d2; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .btn:hover { background: #1565c0; }
    </style>
</head>
<body>
    <div class='error-container'>
        <h1 class='error-header'>{$errorTitle}</h1>
        <div class='error-message'>{$errorMessage}</div>
        <div class='error-actions'>
            <a href='javascript:history.back()' class='btn'>Go Back</a>
            <a href='/' class='btn'>Home</a>
        </div>
    </div>
</body>
</html>";
    }
    
    exit;
}

/**
 * Global error handler for PHP errors
 */
function globalErrorHandler($severity, $message, $file, $line)
{
    // Don't handle error if error reporting is turned off
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    // Log the error
    error_log("PHP Error: [$severity] $message in $file:$line");
    
    // Convert to exception for consistent handling
    throw new ErrorException($message, 0, $severity, $file, $line);
}

/**
 * Shutdown function to catch fatal errors
 */
function globalShutdownHandler()
{
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        // Log fatal error
        error_log("Fatal error: {$error['message']} in {$error['file']}:{$error['line']}");
        
        // Check if this is an API request
        $isApiRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        $isApiRequest = $isApiRequest || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false);
        
        if ($isApiRequest) {
            // Clear any output buffer
            if (ob_get_length()) ob_clean();
            
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'A fatal error occurred',
                'error_code' => 'FATAL_ERROR'
            ]);
        } else {
            // Clear any output buffer
            if (ob_get_length()) ob_clean();
            
            http_response_code(500);
            echo "<!DOCTYPE html>
<html>
<head><title>Fatal Error</title></head>
<body>
    <h1>Fatal Error</h1>
    <p>A fatal error occurred. Please contact the system administrator.</p>
    <p><a href='/'>Return to Home</a></p>
</body>
</html>";
        }
    }
}

/**
 * Initialize error handlers
 */
function initializeErrorHandlers()
{
    // Set custom exception handler
    set_exception_handler('globalExceptionHandler');
    
    // Set custom error handler
    set_error_handler('globalErrorHandler');
    
    // Set shutdown function for fatal errors
    register_shutdown_function('globalShutdownHandler');
    
    // Configure error reporting based on environment
    if (getenv('APP_ENV') === 'development') {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
    }
}

/**
 * Helper function to create standardized API error responses with sanitization
 */
function createApiErrorResponse($message, $errorCode = 'GENERIC_ERROR', $httpCode = 400, $debugInfo = null, $context = null)
{
    $errorSanitizer = ErrorSanitizer::getInstance();
    
    // Sanitize the message and get appropriate HTTP code if not provided
    $sanitizedMessage = $errorSanitizer->sanitize($message, $context);
    if ($httpCode === 400) {
        $httpCode = $errorSanitizer->getHttpStatusCode($message, $context);
    }
    
    $response = [
        'status' => 'error',
        'message' => $sanitizedMessage,
        'error_code' => $errorCode
    ];
    
    // Add debug info in development mode
    if (getenv('APP_ENV') === 'development') {
        if ($debugInfo) {
            $response['debug'] = $debugInfo;
        }
        if ($message !== $sanitizedMessage) {
            $response['debug']['original_message'] = $message;
            $response['debug']['sanitized'] = true;
        }
    }
    
    header('Content-Type: application/json');
    http_response_code($httpCode);
    
    return json_encode($response);
}

/**
 * Helper function to handle validation errors consistently
 */
function handleValidationError($errors, $title = 'Validation Error')
{
    if (is_string($errors)) {
        $errors = [$errors];
    }
    
    $isApiRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    
    if ($isApiRequest) {
        return createApiErrorResponse(
            implode('; ', $errors),
            'VALIDATION_ERROR',
            422
        );
    } else {
        // For web requests, set session error and redirect back
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['validation_errors'] = $errors;
        $_SESSION['validation_title'] = $title;
        
        // Redirect back or to a safe page
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $referer");
        exit;
    }
}