<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Cache-Control: public, max-age=3600'); // 1 hour cache

try {
    $requestedLang = $_GET['lang'] ?? $lang->getCurrentLanguage();
    
    // Validate requested language
    if (!in_array($requestedLang, $lang->getAvailableLanguages())) {
        $requestedLang = $lang->getCurrentLanguage();
    }
    
    // Load specific language
    $langFile = __DIR__ . '/../lang/' . $requestedLang . '.php';
    if (!file_exists($langFile)) {
        throw new Exception('Language file not found');
    }
    
    $translations = require $langFile;
    
    // Filter translations for JavaScript usage
    // Extract commonly used frontend translations
    $frontendTranslations = [
        'language_name' => $translations['language_name'] ?? $requestedLang,
        'auth' => $translations['auth'] ?? [],
        'income' => [
            'title' => $translations['income']['title'] ?? 'Income',
            'add_success' => $translations['income']['add_success'] ?? 'Income added successfully',
            'add_error' => $translations['income']['add_error'] ?? 'Error adding income',
            'edit_success' => $translations['income']['edit_success'] ?? 'Income updated successfully',
            'edit_error' => $translations['income']['edit_error'] ?? 'Error updating income',
            'delete_success' => $translations['income']['delete_success'] ?? 'Income deleted successfully',
            'delete_error' => $translations['income']['delete_error'] ?? 'Error deleting income',
            'delete_confirm' => $translations['income']['delete_confirm'] ?? 'Are you sure you want to delete this income?',
            'not_found' => $translations['income']['not_found'] ?? 'No income found',
            'load_error' => $translations['income']['load_error'] ?? 'Error loading incomes',
            'mark_as_received' => $translations['income']['mark_as_received'] ?? 'Mark as received',
            'mark_not_received' => $translations['income']['mark_not_received'] ?? 'Mark as not received'
        ],
        'payment' => [
            'title' => $translations['payment']['title'] ?? 'Payment',
            'add_success' => $translations['payment']['add_success'] ?? 'Payment added successfully',
            'add_error' => $translations['payment']['add_error'] ?? 'Error adding payment',
            'edit_success' => $translations['payment']['edit_success'] ?? 'Payment updated successfully',
            'edit_error' => $translations['payment']['edit_error'] ?? 'Error updating payment',
            'delete_success' => $translations['payment']['delete_success'] ?? 'Payment deleted successfully',
            'delete_error' => $translations['payment']['delete_error'] ?? 'Error deleting payment',
            'delete_confirm' => $translations['payment']['delete_confirm'] ?? 'Are you sure you want to delete this payment?',
            'not_found' => $translations['payment']['not_found'] ?? 'No payment found',
            'load_error' => $translations['payment']['load_error'] ?? 'Error loading payments',
            'mark_paid' => $translations['payment']['mark_paid'] ?? 'Mark as paid',
            'mark_not_paid' => $translations['payment']['mark_not_paid'] ?? 'Mark as not paid',
            'transfer' => $translations['payment']['transfer'] ?? 'Transfer to next month'
        ],
        'saving' => [
            'title' => $translations['saving']['title'] ?? 'Saving',
            'add_success' => $translations['saving']['add_success'] ?? 'Saving added successfully',
            'add_error' => $translations['saving']['add_error'] ?? 'Error adding saving',
            'edit_success' => $translations['saving']['edit_success'] ?? 'Saving updated successfully',
            'edit_error' => $translations['saving']['edit_error'] ?? 'Error updating saving',
            'delete_success' => $translations['saving']['delete_success'] ?? 'Saving deleted successfully',
            'delete_error' => $translations['saving']['delete_error'] ?? 'Error deleting saving',
            'delete_confirm' => $translations['saving']['delete_confirm'] ?? 'Are you sure you want to delete this saving?',
            'not_found' => $translations['saving']['not_found'] ?? 'No saving found',
            'load_error' => $translations['saving']['load_error'] ?? 'Error loading savings'
        ],
        'app' => [
            'loading' => $translations['app']['loading'] ?? 'Loading...',
            'no_data' => $translations['app']['no_data'] ?? 'No data found',
            'confirm_delete' => $translations['app']['confirm_delete'] ?? 'Are you sure you want to delete?',
            'yes_delete' => $translations['app']['yes_delete'] ?? 'Yes, Delete',
            'no_cancel' => $translations['app']['no_cancel'] ?? 'No, Cancel',
            'operation_success' => $translations['app']['operation_success'] ?? 'Operation successful',
            'operation_error' => $translations['app']['operation_error'] ?? 'Operation error',
            'save_success' => $translations['app']['save_success'] ?? 'Successfully saved',
            'save_error' => $translations['app']['save_error'] ?? 'Error saving',
            'update_success' => $translations['app']['update_success'] ?? 'Successfully updated',
            'update_error' => $translations['app']['update_error'] ?? 'Error updating',
            'delete_success' => $translations['app']['delete_success'] ?? 'Successfully deleted',
            'delete_error' => $translations['app']['delete_error'] ?? 'Error deleting'
        ],
        'validation' => $translations['validation'] ?? [],
        'utils' => $translations['utils'] ?? [],
        'currency' => [
            'invalid_currency' => $translations['currency']['invalid_currency'] ?? 'Invalid currency',
            'update_success' => $translations['currency']['update_success'] ?? 'Currency updated successfully',
            'update_error' => $translations['currency']['update_error'] ?? 'Error updating currency',
            'rate_fetched' => $translations['currency']['rate_fetched'] ?? 'Exchange rate fetched successfully',
            'rate_fetch_error' => $translations['currency']['rate_fetch_error'] ?? 'Could not retrieve exchange rate',
            'select_currency' => $translations['currency']['select_currency'] ?? 'Select Currency',
            'current_rate' => $translations['currency']['current_rate'] ?? 'Current Rate'
        ],
        'settings' => [
            'save_success' => $translations['settings']['save_success'] ?? 'Settings saved successfully',
            'save_error' => $translations['settings']['save_error'] ?? 'Error saving settings',
            'language' => $translations['settings']['language'] ?? 'Language'
        ],
        'frequency' => $translations['frequency'] ?? [],
        'months' => $translations['months'] ?? [],
        'currencies' => $translations['currencies'] ?? [],
        'rate_limit' => $translations['rate_limit'] ?? [],
        
        // Common UI elements
        'save' => $translations['save'] ?? 'Save',
        'cancel' => $translations['cancel'] ?? 'Cancel',
        'delete' => $translations['delete'] ?? 'Delete',
        'edit' => $translations['edit'] ?? 'Edit',
        'update' => $translations['update'] ?? 'Update',
        'yes' => $translations['yes'] ?? 'Yes',
        'no' => $translations['no'] ?? 'No',
        'confirm' => $translations['confirm'] ?? 'Confirm',
        'error' => $translations['error'] ?? 'Error!',
        'success' => $translations['success'] ?? 'Success!',
        'warning' => $translations['warning'] ?? 'Warning!',
        'info' => $translations['info'] ?? 'Info'
    ];
    
    $response = [
        'status' => 'success',
        'language' => $requestedLang,
        'language_name' => $lang->getLanguageName($requestedLang),
        'translations' => $frontendTranslations,
        'timestamp' => time()
    ];
    
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'language' => $lang->getCurrentLanguage(),
        'translations' => []
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>