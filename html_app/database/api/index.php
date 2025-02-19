<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow CORS for same origin
header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
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
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Income.php';
require_once __DIR__ . '/../models/Saving.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/BudgetGoal.php';
require_once __DIR__ . '/../models/ExchangeRate.php';

// Parse JSON request body
$requestBody = file_get_contents('php://input');
$requestData = !empty($requestBody) ? json_decode($requestBody, true) : [];

// Get request path and method
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove base path if exists
$basePath = '/api';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Default user ID (for now)
$userId = 1;

// Simple router
try {
    $response = null;
    $pathParts = explode('/', trim($path, '/'));
    $resource = $pathParts[0] ?? '';

    switch ($resource) {
        case 'payments':
            require_once __DIR__ . '/controllers/PaymentController.php';
            $controller = new PaymentController($userId);
            $response = $controller->handleRequest($method, array_slice($pathParts, 1), $requestData);
            break;

        case 'incomes':
            require_once __DIR__ . '/controllers/IncomeController.php';
            $controller = new IncomeController($userId);
            $response = $controller->handleRequest($method, array_slice($pathParts, 1), $requestData);
            break;

        case 'savings':
            require_once __DIR__ . '/controllers/SavingController.php';
            $controller = new SavingController($userId);
            $response = $controller->handleRequest($method, array_slice($pathParts, 1), $requestData);
            break;

        case 'categories':
            require_once __DIR__ . '/controllers/CategoryController.php';
            $controller = new CategoryController($userId);
            $response = $controller->handleRequest($method, array_slice($pathParts, 1), $requestData);
            break;

        case 'budget-goals':
            require_once __DIR__ . '/controllers/BudgetGoalController.php';
            $controller = new BudgetGoalController($userId);
            $response = $controller->handleRequest($method, array_slice($pathParts, 1), $requestData);
            break;

        case 'exchange-rates':
            require_once __DIR__ . '/controllers/ExchangeRateController.php';
            $controller = new ExchangeRateController();
            $response = $controller->handleRequest($method, array_slice($pathParts, 1), $requestData);
            break;

        default:
            throw new Exception('Unknown resource: ' . $resource);
    }

    // Send response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
