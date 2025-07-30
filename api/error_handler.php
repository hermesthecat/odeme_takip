<?php

/**
 * Global Error Handler
 * @author A. Kerem Gök
 * 
 * Bu dosya global error handling sağlar.
 * Production'da user-friendly error messages,
 * development'da detailed error information.
 */

/**
 * Global exception handler
 */
function globalExceptionHandler($exception)
{
    // Log the error
    error_log("Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
    
    // Check if this is an AJAX/API request
    $isApiRequest = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    $isApiRequest = $isApiRequest || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false);
    
    if ($isApiRequest) {
        // API/AJAX request - return JSON error
        header('Content-Type: application/json');
        http_response_code(500);
        
        $response = [
            'status' => 'error',
            'message' => 'An unexpected error occurred',
            'error_code' => 'INTERNAL_SERVER_ERROR'
        ];
        
        // In development, add more details
        if (getenv('APP_ENV') === 'development') {
            $response['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }
        
        echo json_encode($response);
    } else {
        // Web request - return HTML error page
        http_response_code(500);
        $errorTitle = "System Error";
        $errorMessage = "An unexpected error occurred. Please try again later.";
        
        if (getenv('APP_ENV') === 'development') {
            $errorMessage .= "<br><br><strong>Debug Info:</strong><br>";
            $errorMessage .= "Message: " . htmlspecialchars($exception->getMessage()) . "<br>";
            $errorMessage .= "File: " . htmlspecialchars($exception->getFile()) . "<br>";
            $errorMessage .= "Line: " . $exception->getLine() . "<br>";
        }
        
        echo "<!DOCTYPE html>
<html>
<head>
    <title>{$errorTitle}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .error-container { max-width: 600px; margin: 0 auto; }
        .error-header { color: #d32f2f; border-bottom: 2px solid #d32f2f; padding-bottom: 10px; }
        .error-message { margin: 20px 0; line-height: 1.6; }
        .error-actions { margin-top: 30px; }
        .btn { padding: 10px 20px; background: #1976d2; color: white; text-decoration: none; border-radius: 4px; }
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
 * Helper function to create standardized API error responses
 */
function createApiErrorResponse($message, $errorCode = 'GENERIC_ERROR', $httpCode = 400, $debugInfo = null)
{
    $response = [
        'status' => 'error',
        'message' => $message,
        'error_code' => $errorCode
    ];
    
    if ($debugInfo && getenv('APP_ENV') === 'development') {
        $response['debug'] = $debugInfo;
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