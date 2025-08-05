<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['language'])) {
        throw new Exception(t('validation.field_required', ['field' => 'Language']));
    }

    $language = trim($_POST['language']);
    
    // Language class instance
    $lang = Language::getInstance();
    
    // Validate language
    if (!in_array($language, $lang->getAvailableLanguages())) {
        throw new Exception(t('currency.invalid_currency')); // Reuse existing translation
    }
    
    // Set language
    if ($lang->setLanguage($language)) {
        // If user is logged in, update their language preference in database
        if (isset($_SESSION['user_id'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET language = ? WHERE id = ?");
                $stmt->execute([$language, $_SESSION['user_id']]);
            } catch (Exception $e) {
                // Log error but don't fail the request
                error_log("Failed to update user language preference: " . $e->getMessage());
            }
        }
        
        $response = [
            'status' => 'success',
            'message' => t('settings.save_success'),
            'data' => [
                'language' => $language,
                'language_name' => $lang->getLanguageName($language)
            ]
        ];
    } else {
        throw new Exception(t('settings.save_error'));
    }
    
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);
?>