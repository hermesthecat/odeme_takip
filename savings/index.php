<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow CORS for same origin
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
header("Access-Control-Allow-Origin: {$origin}");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Load required files
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../models/Saving.php';
require_once __DIR__ . '/../common.php';
require_once __DIR__ . '/../controllers/SavingController.php';

// Parse JSON request body
$requestBody = file_get_contents('php://input');
$requestData = !empty($requestBody) ? json_decode($requestBody, true) : [];

// Get query parameters
$queryData = $_GET;

// Merge request body and query parameters
$requestData = array_merge($requestData, $queryData);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Default user ID (for now)
$userId = 1;

try {
    $controller = new SavingController($userId);
    $response = $controller->handleRequest($method, [], $requestData);

    // Send response
    sendJsonResponse($response);
} catch (Exception $e) {
    // Handle errors
    sendErrorResponse($e->getMessage());
}