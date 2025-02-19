<?php
// Set default timezone
date_default_timezone_set('Europe/Istanbul');

// Load error handler
require_once __DIR__ . '/error-handler.php';
ErrorHandler::init();

// Common response function
function sendJsonResponse($data, $status = 200)
{
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code($status);
    }

    echo json_encode([
        'success' => $status >= 200 && $status < 300,
        'data' => $data,
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
    exit;
}

// Common error response function
function sendErrorResponse($message, $code = 500, $details = null)
{
    $response = [
        'success' => false,
        'error' => [
            'message' => $message,
            'code' => $code
        ],
        'timestamp' => date('c')
    ];

    if ($details !== null) {
        $response['error']['details'] = $details;
    }

    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code($code);
    }

    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Database connection validation
function validateDatabaseConnection()
{
    try {
        $db = Database::getInstance();
        $db->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        sendErrorResponse(
            'Database connection failed',
            500,
            ['error' => $e->getMessage()]
        );
    }
}

// Input validation function
function validateRequiredParams($data, $required)
{
    $missing = [];
    foreach ($required as $param) {
        if (!isset($data[$param]) || (empty($data[$param]) && $data[$param] !== '0')) {
            $missing[] = $param;
        }
    }

    if (!empty($missing)) {
        sendErrorResponse(
            'Missing required parameters',
            400,
            ['missing' => $missing]
        );
    }

    return true;
}

// Clean input data
function cleanInput($data)
{
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }

    if (is_string($data)) {
        $data = trim($data);
        $data = stripslashes($data);
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    return $data;
}

// CORS headers for API requests
function setCorsHeaders()
{
    if (isset($_SERVER['HTTP_ORIGIN'])) {
    }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        }

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        }

        exit(0);
    }
}

// Set default headers for all responses
setCorsHeaders();

// Get JSON request body
function getRequestBody()
{
    $input = file_get_contents('php://input');
    if (empty($input)) {
        return [];
    }

    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse(
            'Invalid JSON data',
            400,
            ['error' => json_last_error_msg()]
        );
    }

    return cleanInput($data);
}

// Get query parameters
function getQueryParams()
{
    return cleanInput($_GET);
}

// Initialize request handling
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestBody = $requestMethod !== 'GET' ? getRequestBody() : [];
$queryParams = getQueryParams();
