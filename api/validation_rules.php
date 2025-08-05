<?php
/**
 * API endpoint for client-side validation rules
 * Returns validation rules for JavaScript consumption
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/validate.php';
checkLogin();

header('Content-Type: application/json');

try {
    $formType = $_GET['form_type'] ?? null;
    $rules = getClientValidationRules($formType);
    
    echo json_encode([
        'status' => 'success',
        'data' => $rules
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}